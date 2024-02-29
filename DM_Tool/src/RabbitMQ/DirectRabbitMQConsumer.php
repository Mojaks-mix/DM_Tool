<?php

namespace src\RabbitMQ;

use src\Contracts\ConsumerInterface;

class DirectRabbitMQConsumer extends RabbitMQ implements ConsumerInterface
{
    public function __construct()
    {
        parent::__construct();
        $this->setExchange('email_task', 'direct');
        $this->setQueue(['direct_emial_queue', false, true, false, false]);
        $this->channel->queue_bind('direct_emial_queue', 'email_task', 'VAS');
        $this->channel->basic_qos(null, 1, false);
    }

    public function consume(callable $msgAction)
    {
        $this->channel->basic_consume(
            'direct_emial_queue',
            '',
            false,
            false,
            false,
            false,
            $msgAction
        );

        try {
            $this->channel->consume();
        } catch (\Throwable $exception) {
            echo $exception->getMessage();
        }
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}