<?php
/**
 * This file is part of the logistics project
 *
 * (c) Sergey Kolodyazhnyy <sergey.kolodyazhnyy@gmail.com>
 *
 */

namespace Bcn\Extension\RabbitMq\Container;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RabbitMqExtension extends Extension
{
    /**
     * @return string
     */
    public function getAlias()
    {
        return 'rabbitmq';
    }

    /**
     * @param  array            $configs
     * @param  ContainerBuilder $container
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->addDefinitions(array(
            'rabbitmq.connection' => $this->getConnectionService($config),
            'rabbitmq.channel'    => $this->getChannelService(),
            'rabbitmq.manager'    => $this->getManagerService($config),
        ));

        $container->addDefinitions($this->getExchangeServices($config['exchanges']));
        $container->addDefinitions($this->getQueueServices($config['queues']));
        $container->addDefinitions($this->getConsumerServices($config['consumers']));
        $container->addDefinitions($this->getProducerServices($config['producers']));
    }

    /**
     * @param  array      $config
     * @return Definition
     */
    protected function getManagerService(array $config)
    {
        $definition = new Definition('Bcn\Extension\RabbitMq\Manager', array(new Reference('rabbitmq.channel')));
        foreach ($config['exchanges'] as $name => $parameters) {
            $definition->addMethodCall('addExchange', array($this->getExchangeDefinition($name, $parameters)));
        }

        foreach ($config['queues'] as $name => $parameters) {
            $definition->addMethodCall('addQueue', array($this->getQueueDefinition($name, $parameters)));
        }

        foreach ($config['bindings'] as $parameters) {
            $definition->addMethodCall('bind', array($parameters['exchange'], $parameters['queue']));
        }

        return $definition;
    }

    /**
     * @param  array      $config
     * @return Definition
     */
    protected function getConnectionService(array $config)
    {
        return new Definition('PhpAmqpLib\Connection\AMQPConnection', array(
            $config['host'],
            $config['port'],
            $config['user'],
            $config['password'],
            $config['vhost'],
        ));
    }

    /**
     * @return Definition
     */
    protected function getChannelService()
    {
        $definition = new Definition('PhpAmqpLib\Connection\AMQPChannel');
        $definition->setFactory(array(new Reference("rabbitmq.connection"), "channel"));

        return $definition;
    }

    /**
     * @param  array $consumers
     * @return array
     */
    protected function getExchangeServices(array $consumers)
    {
        $manager = new Reference('rabbitmq.manager');
        $definitions = array();
        foreach ($consumers as $name => $config) {
            $definition = new Definition('Bcn\Extension\RabbitMq\Exchange', array($name));
            $definition->setPublic(false);
            $definition->setFactory(array($manager, 'getExchange'));

            $definitions['rabbitmq.exchange.'.$name] = $definition;
        }

        return $definitions;
    }
    /**
     * @param  array $consumers
     * @return array
     */
    protected function getQueueServices(array $consumers)
    {
        $manager = new Reference('rabbitmq.manager');
        $definitions = array();
        foreach ($consumers as $name => $config) {
            $definition = new Definition('Bcn\Extension\RabbitMq\Queue', array($name));
            $definition->setPublic(false);
            $definition->setFactory(array($manager, 'getQueue'));

            $definitions['rabbitmq.queue.'.$name] = $definition;
        }

        return $definitions;
    }
    /**
     * @param  array $consumers
     * @return array
     */
    protected function getConsumerServices(array $consumers)
    {
        $channel = new Reference('rabbitmq.channel');
        $definitions = array();
        foreach ($consumers as $name => $config) {
            $service = new Reference($config['service']);
            $queue = new Reference("rabbitmq.queue.".$config['queue']);
            $definition = new Definition('Bcn\Extension\RabbitMq\Consumer', array($channel, $queue, $service, $config['tag']));

            $definitions['rabbitmq.consumer.'.$name] = $definition;
        }

        return $definitions;
    }

    /**
     * @param  array $producers
     * @return array
     */
    protected function getProducerServices(array $producers)
    {
        $channel = new Reference('rabbitmq.channel');
        $definitions = array();
        foreach ($producers as $name => $config) {
            $exchange = new Reference('rabbitmq.exchange.'.$config['exchange']);
            $definition = new Definition('Bcn\Extension\RabbitMq\Producer', array($channel, $exchange));
            $definitions['rabbitmq.producer.'.$name] = $definition;
        }

        return $definitions;
    }

    /**
     * @param  string     $name
     * @param  array      $config
     * @return Definition
     */
    protected function getQueueDefinition($name, array $config)
    {
        $manager = new Reference('rabbitmq.channel');

        return new Definition('Bcn\Extension\RabbitMq\Queue', array($manager, $name));
    }

    /**
     * @param  string     $name
     * @param  array      $config
     * @return Definition
     */
    protected function getExchangeDefinition($name, array $config)
    {
        $manager = new Reference('rabbitmq.channel');

        return new Definition('Bcn\Extension\RabbitMq\Exchange', array($manager, $name, $config['type']));
    }
}
