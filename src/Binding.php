<?php
/**
 * This file is part of the payments project
 *
 * (c) Sergey Kolodyazhnyy <sergey.kolodyazhnyy@gmail.com>
 *
 */

namespace Bcn\Extension\RabbitMq;

use PhpAmqpLib\Channel\AMQPChannel;

class Binding
{
    /** @var Queue */
    private $queue;

    /** @var Exchange */
    private $exchange;

    /** @var AMQPChannel */
    private $channel;

    /** @var boolean */
    private $initialized = false;

    /**
     * @param AMQPChannel $channel
     * @param Exchange    $exchange
     * @param Queue       $queue
     */
    public function __construct(AMQPChannel $channel, Exchange $exchange, Queue $queue)
    {
        $this->channel  = $channel;
        $this->exchange = $exchange;
        $this->queue    = $queue;
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

        $this->exchange->initialize();
        $this->queue->initialize();

        $this->channel->queue_bind(
            $this->queue->name(),
            $this->exchange->name()
        );
    }
}
