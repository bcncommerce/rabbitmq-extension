<?php
/**
 * This file is part of the payments project
 *
 * (c) Sergey Kolodyazhnyy <sergey.kolodyazhnyy@gmail.com>
 *
 */

namespace Bcn\Extension\RabbitMq;

use PhpAmqpLib\Channel\AMQPChannel;

class Queue
{
    /** @var bool */
    private $initialized = false;

    /** @var AMQPChannel */
    private $channel;

    /** @var Binding[] */
    private $bindings = array();

    /** @var string */
    private $name;

    /** @var bool */
    private $passive = false;

    /** @var bool */
    private $durable = true;

    /** @var bool */
    private $exclusive = false;

    /** @var bool */
    private $autoDelete = false;

    /** @var bool */
    private $noWait = false;

    /**
     * @param AMQPChannel $channel
     * @param string      $name
     */
    public function __construct(AMQPChannel $channel, $name)
    {
        $this->channel  = $channel;
        $this->name     = $name;
    }

    /**
     * @param  Binding $binding
     * @return $this
     */
    public function bind(Binding $binding)
    {
        $this->bindings[] = $binding;

        return $this;
    }

    /**
     * @return void
     */
    public function initialize()
    {
        if ($this->initialized) {
            return;
        }

        $this->initialized = true;

        $this->channel->queue_declare(
            $this->name,
            $this->passive,
            $this->durable,
            $this->exclusive,
            $this->autoDelete,
            $this->noWait
        );

        foreach ($this->bindings as $binding) {
            $binding->initialize();
        }
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }
}
