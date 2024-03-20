<?php

namespace src\RabbitMQ;

use PhpAmqpLib\Connection\AMQPStreamConnection;

abstract class RabbitMQ
{
    protected $connection;
    protected $channel;
    protected $queue;
    protected string $exchange      = '';
    protected string $exchangeType  = 'fanout';

    public function __construct()
    {
        $this->connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
        $this->channel      = $this->connection->channel();
    }

    protected function setQueue(array $queueConf)
    {
        $this->queue = $queueConf[0];
        $this->channel->queue_declare(...$queueConf);
    }

    protected function setExchange(string $exchangeName, string $exchangeType)
    {
        $this->exchange     = $exchangeName;
        $this->exchangeType = $exchangeType;
        $this->channel->exchange_declare($exchangeName, $exchangeType);
    }

    public function isJson($string)
    {
        if (!is_string($string)) {
            return false; // Not a string, cannot be JSON
        }

        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}