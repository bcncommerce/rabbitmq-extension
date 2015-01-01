<?php
/**
 * This file is part of the payments project
 *
 * (c) Sergey Kolodyazhnyy <sergey.kolodyazhnyy@gmail.com>
 *
 */

namespace Bcn\Extension\RabbitMq\Consumer;

use PhpAmqpLib\Message\AMQPMessage;

class NullConsumer implements ConsumerInterface
{
    public function execute(AMQPMessage $message)
    {
    }
}
