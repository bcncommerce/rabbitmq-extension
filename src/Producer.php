<?php
/**
 * This file is part of the payments project
 *
 * (c) Sergey Kolodyazhnyy <sergey.kolodyazhnyy@gmail.com>
 *
 */

namespace Bcn\Extension\RabbitMq;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class Producer
{
    /** @var AMQPChannel */
    private $channel;

    /** @var Exchange */
    private $exchange;

    /**
     * @param AMQPChannel $channel
     * @param Exchange    $exchange
     */
    public function __construct(AMQPChannel $channel, Exchange $exchange)
    {
        $this->channel = $channel;
        $this->exchange = $exchange;
    }

    /**
     * @param string $body
     * @param string $routingKey
     * @param array  $properties
     */
    public function publish($body, $routingKey = null, array $properties = array())
    {
        $this->exchange->initialize();

        $message = new AMQPMessage($body, $properties);

        $this->channel->basic_publish(
            $message,
            $this->exchange->name(),
            $routingKey
        );
    }
}
