<?php
/**
 * This file is part of the payments project
 *
 * (c) Sergey Kolodyazhnyy <sergey.kolodyazhnyy@gmail.com>
 *
 */

namespace Bcn\Extension\RabbitMq;

use PhpAmqpLib\Channel\AMQPChannel;

class Exchange
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

    /** @var string */
    private $type;

    /** @var bool */
    private $autoDelete = false;

    /**
     * @param AMQPChannel $channel
     * @param string      $name
     * @param string      $type
     */
    public function __construct(AMQPChannel $channel, $name, $type = 'direct')
    {
        $this->channel  = $channel;
        $this->name     = $name;
        $this->type     = $type;
        $this->bindings = array();
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

        $this->channel->exchange_declare(
            $this->name,
            $this->type,
            $this->passive,
            $this->durable,
            $this->autoDelete
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
