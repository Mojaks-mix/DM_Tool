<?php

namespace src\RabbitMQ;

use PhpAmqpLib\Message\AMQPMessage;
use src\Contracts\ProducerInterface;
use src\Exceptions\CustomException;

class BasicRabbitMQProducer extends RabbitMQ implements ProducerInterface
{
    private array $messageOptions   = [];

    public function __construct()
    {
        parent::__construct();
        $this->setQueue(['task_queue', false, true, false, false]);
        $this->messageOptions = ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT];
    }

    public function produce(mixed $data)
    {
        if ($data !== null) {
            if (!$this->isJson($data)) {
                $data = json_encode($data);
            }
            $msg = new AMQPMessage($data, $this->messageOptions);
            $this->channel->basic_publish($msg, $this->exchange, $this->queue);
        } else {
            throw new CustomException("Data message cannot be empty!");
        }
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}