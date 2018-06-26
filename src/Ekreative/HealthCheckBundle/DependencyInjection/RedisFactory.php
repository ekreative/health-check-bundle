<?php

namespace Ekreative\HealthCheckBundle\DependencyInjection;

class RedisFactory
{
    /**
     * @param string $host
     * @param int    $port
     * @param int    $timeout
     * @param string $prefix
     *
     * @return \Redis
     */
    public static function get($host, $port = 6739, $timeout = 5, $prefix = null)
    {
        set_error_handler(function ($severity, $message, $file, $line) {
            if ($severity & error_reporting()) {
                throw new \ErrorException($message, 0, $severity, $file, $line);
            }
        });

        $redis = new \Redis();
        try {
            $redis->connect($host, $port, $timeout);
            if ($prefix) {
                $redis->setOption(\Redis::OPT_PREFIX, $prefix);
            }
        } catch (\RedisException $e) {
        } catch (\ErrorException $e) {
        }

        restore_error_handler();

        return $redis;
    }
}
