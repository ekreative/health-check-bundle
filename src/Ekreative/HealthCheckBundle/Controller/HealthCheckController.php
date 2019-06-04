<?php

namespace Ekreative\HealthCheckBundle\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    
    /**
     * 
     */
    private $rabbitmq;

    public function __construct(ManagerRegistry $doctrine, array $connections, 
    $optionalConnections, array $redis, array $optionalRedis
    ,array $rabbitmq
    )
    {
// var_dump('const');die();

        $this->doctrine = $doctrine;
        $this->connections = $connections;
        $this->optionalConnections = $optionalConnections;
        $this->redis = $redis;
        $this->optionalRedis = $optionalRedis;
        $this->rabbitmq = $rabbitmq;
    }

    /**
     * @Route("/healthcheck", name="health-check", methods={"GET"})
     */
    public function healthCheckAction()
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
                $key .= "$i";
            }

            foreach ($this->connections as $name) {
                $data[$key] = $required[$key] = $this->checkDoctrineConnection($this->doctrine->getConnection($name));
                ++$i;
                $key = "database$i";
            }

            foreach ($this->optionalConnections as $name) {
                $data[$key] = $this->checkDoctrineConnection($this->doctrine->getConnection($name));
                ++$i;
                $key = "database$i";
            }

            if (!count($this->connections) && !count($this->optionalConnections)) {
                $data[$key] = $required[$key] = $this->checkDoctrineConnection($this->doctrine->getConnection());
            }
        }

        set_error_handler(function ($errno, $errstr, $errfile, $errline, array $errcontext) {
            // error was suppressed with the @-operator
            if (0 === error_reporting()) {
                return false;
            }

            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        $i = 0;
        $key = 'redis';
        if ((count($this->redis) + count($this->optionalRedis)) > 1) {
            $key .= "$i";
        }
        foreach ($this->redis as $redis) {
            $data[$key] = $required[$key] = $this->checkRedisConnection($redis);
            ++$i;
            $key = "redis$i";
        }
        foreach ($this->optionalRedis as $redis) {
            $data[$key] = $this->checkRedisConnection($redis);
            ++$i;
            $key = "redis$i";
        }

        restore_error_handler();

        if($this->rabbitmq){
            $check = $this->checkRabbitmqConnection($this->rabbitmq);
            var_dump($check);die();
        }

        $ok = array_reduce($required, function ($m, $v) {
            return $m && $v;
        }, true);

        return new JsonResponse($data, $ok ? 200 : 503);
    }

    /**
     * @return bool
     */
    private function checkDoctrineConnection(Connection $connection)
    {
        try {
            return $connection->ping();
        } catch (\Exception $e) {
            return false;
        }
    }
    
        /**
         * @param \Redis $redis
         *
         * @return bool
         */
        private function checkRedisConnection($redis)
        {
            try {
                $redis->ping();
    
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }
    
        /**
         * @param $rabbitmq
         *
         * @return bool
         */
        private function checkRabbitmqConnection($rabbitmq)
        {
            try {
                var_dump('checkl');die();
                

                return true;
            } catch (\Exception $e) {
                return false;
            }
        }
}
