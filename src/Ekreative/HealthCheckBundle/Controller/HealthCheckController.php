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

        if ($this->container->has('doctrine') && $this->container->getParameter('ekreative_health_check.doctrine_enabled')) {
            $doctrine = $this->getDoctrine();
            $connections = $this->container->getParameter('ekreative_health_check.doctrine');
            if (count($connections)) {
                $i = 0;
                foreach ($this->container->getParameter('ekreative_health_check.doctrine_connections') as $name) {
                    $data["database$i"] = $this->checkDoctrineConnection($doctrine->getConnection($name));
                    ++$i;
                }
            } else {
                $data['database'] = $this->checkDoctrineConnection($doctrine->getConnection());
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
        $i = 0;
        $key = 'redis';
        if (count($redisServiceNames) > 1) {
            $key .= "$i";
        }
        foreach ($redisServiceNames as $redisService) {
            $data[$key] = $this->checkRedisConnection($redisService);
            ++$i;
            $key = "redis$i";
        }

        restore_error_handler();

        $ok = array_reduce($data, function ($m, $v) {
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
