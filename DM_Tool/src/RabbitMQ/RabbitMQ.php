<?php

namespace src\RabbitMQ;

use PhpAmqpLib\Connection\AMQPStreamConnection;

abstract class RabbitMQ
{
    protected $connection;
    protected $channel;
    protected $queue;
    protected string $exchange        = '';

    public function __construct()
    {
        $this->connection   = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $this->channel      = $this->connection->channel();
    }

    protected function setQueue(array $queueConf)
    {
        $this->queue = $queueConf[0];
        $this->channel->queue_declare(...$queueConf);
    }

    protected function setExchange(string $exchangeName)
    {
        $this->queue = $exchangeName;
    }

    protected function isJson($string)
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