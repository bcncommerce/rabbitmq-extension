<?php
/**
 * This file is part of the payments project
 *
 * (c) Sergey Kolodyazhnyy <sergey.kolodyazhnyy@gmail.com>
 *
 */

namespace Bcn\Extension\RabbitMq;

use PhpAmqpLib\Channel\AMQPChannel;

class Manager
{
    /** @var AMQPChannel */
    private $channel;

    /** @var Queue[] */
    private $queues = array();

    /** @var Exchange[] */
    private $exchanges = array();

    /**
     * @param AMQPChannel $channel
     */
    public function __construct(AMQPChannel $channel)
    {
        $this->channel = $channel;
    }

    /**
     * @param  Queue $queue
     * @return $this
     */
    public function addQueue(Queue $queue)
    {
        if ($this->hasQueue($queue->name())) {
            throw new \InvalidArgumentException(sprintf('Queue "%s" already defined', $queue->name()));
        }

        $this->queues[$queue->name()] = $queue;

        return $this;
    }

    /**
     * @param  string $name
     * @return bool
     */
    public function hasQueue($name)
    {
        return isset($this->queues[$name]);
    }

    /**
     * @param  string $name
     * @return Queue
     */
    public function getQueue($name)
    {
        if (!$this->hasQueue($name)) {
            throw new \InvalidArgumentException(sprintf('Queue "%s" is not defined', $name));
        }

        return $this->queues[$name];
    }

    /**
     * @param  Exchange $exchange
     * @return $this
     */
    public function addExchange(Exchange $exchange)
    {
        if ($this->hasExchange($exchange->name())) {
            throw new \InvalidArgumentException(sprintf('Exchange "%s" already defined', $exchange->name()));
        }

        $this->exchanges[$exchange->name()] = $exchange;

        return $this;
    }

    /**
     * @param  string $name
     * @return bool
     */
    public function hasExchange($name)
    {
        return isset($this->exchanges[$name]);
    }

    /**
     * @param  string   $name
     * @return Exchange
     */
    public function getExchange($name)
    {
        if (!$this->hasExchange($name)) {
            throw new \InvalidArgumentException(sprintf('Exchange "%s" is not defined', $name));
        }

        return $this->exchanges[$name];
    }

    /**
     * @param  string $exchange
     * @param  string $queue
     * @return $this
     */
    public function bind($exchange, $queue)
    {
        $queue = $this->getQueue($queue);
        $exchange = $this->getExchange($exchange);
        $binding = new Binding($this->channel, $exchange, $queue);

        $queue->bind($binding);
        $exchange->bind($binding);

        return $this;
    }
}
