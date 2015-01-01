<?php
/**
 * This file is part of the payments project
 *
 * (c) Sergey Kolodyazhnyy <sergey.kolodyazhnyy@gmail.com>
 *
 */

namespace Bcn\Extension\RabbitMq\Container;

namespace Bcn\Extension\RabbitMq\Container;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $tree->root('rabbitmq')
            ->children()
                ->scalarNode('host')->defaultValue('localhost')->end()
                ->scalarNode('port')->defaultValue(5672)->end()
                ->scalarNode('user')->defaultValue('guest')->end()
                ->scalarNode('password')->defaultValue('guest')->end()
                ->scalarNode('vhost')->defaultValue('/')->end()
                ->arrayNode('queues')
                    ->prototype('array')
                        ->children()
                            ->booleanNode('passive')->defaultFalse()->end()
                            ->booleanNode('durable')->defaultTrue()->end()
                            ->booleanNode('exclusive')->defaultFalse()->end()
                            ->booleanNode('auto_delete')->defaultFalse()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('exchanges')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('type')->defaultValue('direct')->end()
                            ->booleanNode('passive')->defaultFalse()->end()
                            ->booleanNode('durable')->defaultTrue()->end()
                            ->booleanNode('auto_delete')->defaultFalse()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('bindings')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('exchange')->isRequired()->end()
                            ->scalarNode('queue')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('consumers')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('service')->isRequired()->end()
                            ->scalarNode('exchange')->isRequired()->end()
                            ->scalarNode('queue')->isRequired()->end()
                            ->scalarNode('tag')->defaultValue(null)->end()
                            ->booleanNode('no_local')->defaultFalse()->end()
                            ->booleanNode('no_ack')->defaultFalse()->end()
                            ->booleanNode('exclusive')->defaultFalse()->end()
                            ->booleanNode('nowait')->defaultFalse()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('producers')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('exchange')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $tree;
    }
}
