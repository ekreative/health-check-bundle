<?php

namespace Ekreative\HealthCheckBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class EkreativeHealthCheckExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('ekreative_health_check.doctrine_enabled', $config['doctrine_enabled']);
        $container->setParameter('ekreative_health_check.doctrine', $config['doctrine']);
        $container->setParameter('ekreative_health_check.optional_doctrine', $config['optional_doctrine']);
        $container->setParameter('ekreative_health_check.redis', $config['redis']);
        $container->setParameter('ekreative_health_check.optional_redis', $config['optional_redis']);
    }
}
