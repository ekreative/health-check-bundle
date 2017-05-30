<?php

namespace Ekreative\HealthCheckBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ekreative_health_check');

        $rootNode->children()
            ->arrayNode('redis')
                ->prototype('scalar')->end()
            ->end()
            ->arrayNode('doctrine')
                ->prototype('scalar')->end()
            ->end()
            ->booleanNode('doctrine_enabled')
                ->defaultTrue()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
