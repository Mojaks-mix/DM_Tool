<?php

namespace src\Core;

use PhpAmqpLib\Message\AMQPMessage;
use src\RabbitMQ\RabbitMQProducer;

class MQEmailProducer extends RabbitMQProducer
{
    public function __construct()
    {
        parent::__construct();
    }

    public function configuration()
    {
        $this->makeConnection(['localhost', 5672, 'guest', 'guest']);
        $this->makeQueue('task_queue', ['task_queue', false, true, false, false]);
        $this->makeMessageOptions(['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
    }
}
