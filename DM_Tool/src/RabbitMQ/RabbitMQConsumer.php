<?php

namespace src\RabbitMQ;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use src\Contracts\ConsumerInterface;

abstract class RabbitMQConsumer implements ConsumerInterface
{
    private $connection;
    private $channel;
    private $queue;

    public function __construct()
    {
        $this->configuration();
    }

    abstract public function configuration();

    protected function makeConnection(array $connectionConfigs)
    {
        $this->connection   = new AMQPStreamConnection(...$connectionConfigs);
        $this->channel      = $this->connection->channel();
    }

    protected function makeQueue($queueName, array $queueConfigs)
    {
        $this->queue = $queueName;
        $this->channel->queue_declare(...$queueConfigs);
    }

    protected function makeQoS(array $QoSConfigs)
    {
        $this->channel->basic_qos(...$QoSConfigs);
    }

    protected function makeConsumeConfig(array $consumeConfigs)
    {
        $this->channel->basic_consume(...$consumeConfigs);
    }

    public function consume(): mixed
    {
        try {
            $this->channel->consume();
        } catch (\Throwable $exception) {
            echo $exception->getMessage();
        }
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
