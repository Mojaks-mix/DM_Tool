<?php

namespace src\Core;

use src\RabbitMQ\RabbitMQConsumer;

class MQEmailConsumer extends RabbitMQConsumer
{
    public function __construct()
    {
        parent::__construct();
    }

    public function configuration()
    {
        $this->makeConnection(['localhost', 5672, 'guest', 'guest']);
        $this->makeQueue('task_queue', ['task_queue', false, true, false, false]);
        $this->makeQoS([null, 1, false]);
        $this->makeConsumeConfig([
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
                sleep(4);
                echo " [x] Done\n";
                $msg->ack(); //important
            },
        ]);
    }
}
