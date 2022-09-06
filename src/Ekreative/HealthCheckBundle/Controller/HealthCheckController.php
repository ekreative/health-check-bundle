<?php

namespace Ekreative\HealthCheckBundle\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Cache\Traits\RedisProxy;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HealthCheckController
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var string[]
     */
    private $connections;

    /**
     * @var string[]
     */
    private $optionalConnections;

    /**
     * @var \Redis[]
     */
    private $redis;

    /**
     * @var \Redis[]
     */
    private $optionalRedis;

    public function __construct(?ManagerRegistry $doctrine, array $connections, array $optionalConnections, array $redis, array $optionalRedis)
    {
        $this->doctrine = $doctrine;
        $this->connections = $connections;
        $this->optionalConnections = $optionalConnections;
        $this->redis = $redis;
        $this->optionalRedis = $optionalRedis;
    }

    /**
     * @Route("/healthcheck", name="health-check", methods={"GET"})
     */
    public function healthCheckAction(): Response
    {
        $data = [
            'app' => true,
        ];

        $required = [
            'app' => true,
        ];

        if ($this->doctrine) {
            $i = 0;
            $key = 'database';
            if ((count($this->connections) + count($this->optionalConnections)) > 1) {
                $key .= "{$i}";
            }

            foreach ($this->connections as $name) {
                $data[$key] = $required[$key] = $this->checkDoctrineConnection($this->doctrine->getConnection($name));
                ++$i;
                $key = "database{$i}";
            }

            foreach ($this->optionalConnections as $name) {
                $data[$key] = $this->checkDoctrineConnection($this->doctrine->getConnection($name));
                ++$i;
                $key = "database{$i}";
            }

            if (!count($this->connections) && !count($this->optionalConnections)) {
                $data[$key] = $required[$key] = $this->checkDoctrineConnection($this->doctrine->getConnection());
            }
        }

        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            // error was suppressed with the @-operator
            if (0 === error_reporting()) {
                return false;
            }

            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        $i = 0;
        $key = 'redis';
        if ((count($this->redis) + count($this->optionalRedis)) > 1) {
            $key .= "{$i}";
        }
        foreach ($this->redis as $redis) {
            $data[$key] = $required[$key] = $this->checkRedisConnection($redis);
            ++$i;
            $key = "redis{$i}";
        }
        foreach ($this->optionalRedis as $redis) {
            $data[$key] = $this->checkRedisConnection($redis);
            ++$i;
            $key = "redis{$i}";
        }

        restore_error_handler();

        $ok = array_reduce($required, function ($m, $v) {
            return $m && $v;
        }, true);

        return new JsonResponse($data, $ok ? 200 : 503);
    }

    private function checkDoctrineConnection(Connection $connection): bool
    {
        try {
            return (bool) $connection->executeQuery('SELECT 1');
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkRedisConnection(\Redis|\RedisArray|RedisProxy|\Predis\ClientInterface $redis): bool
    {
        try {
            $redis->ping();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
