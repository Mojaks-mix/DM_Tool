<?php

namespace src\RabbitMQ;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use src\Contracts\ProducerInterface;
use src\Exceptions\CustomException;

abstract class RabbitMQProducer implements ProducerInterface
{
    private $connection;
    private $channel;
    private $queue;
    private string $pubType         = '';
    private array $messageOptions   = [];

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
        $this->queue    = $queueName;
        $this->channel->queue_declare(...$queueConfigs);
    }

    protected function makeMessageOptions(array $options)
    {
        $this->messageOptions = $options;
    }

    protected function makePublishType(string $type)
    {
        $this->pubType = $type;
    }

    public function produce(mixed $data)
    {
        if ($data !== null) {
            if (!$this->isJson($data)) {
                $data = json_encode($data);
            }
            $msg = new AMQPMessage($data, $this->messageOptions);
            $this->channel->basic_publish($msg, $this->pubType, $this->queue);
        } else {
            throw new CustomException("Data message cannot be empty!");
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
