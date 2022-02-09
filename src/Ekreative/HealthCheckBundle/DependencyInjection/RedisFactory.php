<?php

namespace Ekreative\HealthCheckBundle\DependencyInjection;

class RedisFactory
{
    /**
     * @throws \ErrorException
     */
    public static function get(string $host, int $port = 6379, int $timeout = 5, string $prefix = null): \Redis
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
        } catch (\RedisException|\ErrorException $e) {
        }

        restore_error_handler();

        return $redis;
    }
}
