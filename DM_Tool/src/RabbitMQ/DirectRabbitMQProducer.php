<?php

namespace src\RabbitMQ;

use PhpAmqpLib\Message\AMQPMessage;
use src\Contracts\ProducerInterface;
use src\Exceptions\CustomException;

class DirectRabbitMQProducer extends RabbitMQ implements ProducerInterface
{
    private array $messageOptions   = [];

    public function __construct()
    {
        parent::__construct();
        $this->setExchange('email_task', 'direct');
        $this->messageOptions = ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT];
    }

    public function produce(mixed $data)
    {
        if ($data !== null) {
            if (!$this->isJson($data)) {
                $data = json_encode($data);
            }
            $msg = new AMQPMessage($data, $this->messageOptions);
            $this->channel->basic_publish($msg, $this->exchange, 'VAS');
        } else {
            throw new CustomException("Data message cannot be empty!");
        }
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}