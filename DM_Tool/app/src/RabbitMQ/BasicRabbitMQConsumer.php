<?php

namespace src\RabbitMQ;

use src\Contracts\ConsumerInterface;

class BasicRabbitMQConsumer extends RabbitMQ implements ConsumerInterface
{
    public function __construct()
    {
        parent::__construct();
        $this->setQueue(['task_queue', false, true, false, false]);
        $this->channel->basic_qos(null, 1, false);
        $this->channel->basic_consume(
            'task_queue',
            '',
            false,
            false,
            false,
            false,
            function ($msg) {
                $message = $msg->getBody();
                if ($this->isJson($message)) {
                    $message = json_decode($message);
                }
                echo ' [x] Received ', var_dump($message), "\n";
                sleep(1);
                echo " [x] Done\n";
                $msg->ack(); //important
            }
        );
    }

    public function consume(): mixed
    {
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