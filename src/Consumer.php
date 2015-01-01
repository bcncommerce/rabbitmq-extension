<?php
/**
 * This file is part of the payments project
 *
 * (c) Sergey Kolodyazhnyy <sergey.kolodyazhnyy@gmail.com>
 *
 */

namespace Bcn\Extension\RabbitMq;

use Bcn\Extension\RabbitMq\Consumer\ConsumerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class Consumer
{
    /** @var AMQPChannel */
    private $channel;

    /** @var string */
    private $tag = null;

    /** @var boolean */
    private $noLocal = false;

    /** @var boolean */
    private $noAck = false;

    /** @var boolean */
    private $exclusive = false;

    /** @var boolean */
    private $noWait = false;

    /** @var Queue */
    private $queue;

    /** @var ConsumerInterface */
    private $service;

    /**
     * @param AMQPChannel       $channel
     * @param Queue             $queue
     * @param ConsumerInterface $consumer
     * @param string            $tag
     */
    public function __construct(AMQPChannel $channel, Queue $queue, ConsumerInterface $consumer, $tag = null)
    {
        $this->channel = $channel;
        $this->queue   = $queue;
        $this->service = $consumer;
        $this->tag     = $tag;
    }

    /**
     *
     */
    public function consume()
    {
        $this->queue->initialize();

        $service = $this->service;

        $this->channel->basic_consume(
            $this->queue->name(),
            $this->tag,
            $this->noLocal,
            $this->noAck,
            $this->exclusive,
            $this->noWait,
            function (AMQPMessage $message) use ($service) {
                $ack = $service->execute($message);

                if ($ack === null || $ack === true) {
                    $message->delivery_info['channel']
                        ->basic_ack($message->delivery_info['delivery_tag']);
                }
            }
        );

        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }
}
