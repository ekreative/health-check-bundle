<?php

namespace Ekreative\HealthCheckBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ekreative_health_check');

        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('ekreative_health_check');
        }

        $rootNode->children()
            ->arrayNode('redis')
            ->prototype('scalar')->end()
            ->end()
            ->arrayNode('optional_redis')
            ->prototype('scalar')->end()
            ->end()
            ->arrayNode('doctrine')
            ->prototype('scalar')->end()
            ->end()
            ->arrayNode('optional_doctrine')
            ->prototype('scalar')->end()
            ->end()
            ->booleanNode('doctrine_enabled')
            ->defaultTrue()
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
