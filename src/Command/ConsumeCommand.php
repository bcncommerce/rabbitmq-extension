<?php
/**
 * This file is part of the payments project
 *
 * (c) Sergey Kolodyazhnyy <sergey.kolodyazhnyy@gmail.com>
 *
 */

namespace Bcn\Extension\RabbitMq\Command;

use Bcn\Component\Console\Commands\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsumeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('rabbitmq:consume')
            ->addArgument("name", InputArgument::REQUIRED, "Consumer name")
            ->setDescription("Start RabbitMQ message consumer")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $service = 'rabbitmq.consumer.'.$name;
        if (!$this->container->has($service)) {
            throw new \InvalidArgumentException(sprintf('Consumer "%s" is not defined', $name));
        }

        $consumer = $this->container->get($service);
        $consumer->consume();
    }
}
