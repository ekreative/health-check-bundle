<?php

namespace Ekreative\HealthCheckBundle\Controller;

use Doctrine\DBAL\Connection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HealthCheckController extends Controller
{
    /**
     * @Route("/healthcheck", name="health-check")
     * @Method({"GET"})
     */
    public function healthCheckAction()
    {
        $data = [
            'app' => true,
        ];

        $required = [
            'app' => true,
        ];

        if ($this->container->has('doctrine') && $this->container->getParameter('ekreative_health_check.doctrine_enabled')) {
            $doctrine = $this->getDoctrine();

            $connections = $this->container->getParameter('ekreative_health_check.doctrine');
            $optionalConnections = $this->container->getParameter('ekreative_health_check.optional_doctrine');
            $i = 0;
            $key = 'database';
            if ((count($connections) + count($optionalConnections)) > 1) {
                $key .= "$i";
            }

            if (count($connections)) {
                foreach ($connections as $name) {
                    $data[$key] = $required[$key] = $this->checkDoctrineConnection($doctrine->getConnection($name));
                    ++$i;
                    $key = "database$i";
                }
            }

            if (count($optionalConnections)) {
                foreach ($optionalConnections as $name) {
                    $data[$key] = $this->checkDoctrineConnection($doctrine->getConnection($name));
                    ++$i;
                    $key = "database$i";
                }
            }

            if (!count($connections) && !count($optionalConnections)) {
                $data[$key] = $required[$key] = $this->checkDoctrineConnection($doctrine->getConnection());
            }
        }

        set_error_handler(function ($errno, $errstr, $errfile, $errline, array $errcontext) {
            // error was suppressed with the @-operator
            if (0 === error_reporting()) {
                return false;
            }

            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        $redisServiceNames = $this->container->getParameter('ekreative_health_check.redis');
        $optionalRedisServiceNames = $this->container->getParameter('ekreative_health_check.optional_redis');

        $i = 0;
        $key = 'redis';
        if ((count($redisServiceNames) + count($optionalRedisServiceNames)) > 1) {
            $key .= "$i";
        }
        foreach ($redisServiceNames as $redisService) {
            $data[$key] = $required[$key] = $this->checkRedisConnection($redisService);
            ++$i;
            $key = "redis$i";
        }
        foreach ($optionalRedisServiceNames as $redisService) {
            $data[$key] = $this->checkRedisConnection($redisService);
            ++$i;
            $key = "redis$i";
        }

        restore_error_handler();

        $ok = array_reduce($required, function ($m, $v) {
            return $m && $v;
        }, true);

        return $this->json($data, $ok ? 200 : 503);
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
     * @param string $redisService
     *
     * @return bool
     */
    private function checkRedisConnection($redisService)
    {
        try {
            $redis = $this->container->get($redisService);
            $redis->ping();

            return true;
        } catch (\RedisException $e) {
            return false;
        } catch (\ErrorException $e) {
            return false;
        }
    }
}
