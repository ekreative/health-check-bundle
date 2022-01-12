<?php

namespace Ekreative\HealthCheckBundle\DependencyInjection;

use Ekreative\HealthCheckBundle\Controller\HealthCheckController;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class EkreativeHealthCheckExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $args = [];
        if ($config['doctrine_enabled']) {
            $args[] = new Reference('doctrine', ContainerInterface::NULL_ON_INVALID_REFERENCE);
        } else {
            $args[] = null;
        }

        $args[] = $config['doctrine'];
        $args[] = $config['optional_doctrine'];

        $args[] = array_map(function ($service) {
            return new Reference($service);
        }, $config['redis']);

        $args[] = array_map(function ($service) {
            return new Reference($service);
        }, $config['optional_redis']);

        $def = new Definition(HealthCheckController::class, $args);
        $def->addTag('controller.service_arguments');

        $container->addDefinitions([HealthCheckController::class => $def]);
    }
}
