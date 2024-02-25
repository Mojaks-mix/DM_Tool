<?php

namespace src\Core;

use src\Contracts\ConsumerInterface;
use src\Contracts\ProducerInterface;
use src\RabbitMQ\BasicRabbitMQProducer;
use src\RabbitMQ\BasicRabbitMQConsumer;

class EmailTask
{
    public function __construct(private ProducerInterface $producer = new BasicRabbitMQProducer(),
        private ConsumerInterface $consumer = new BasicRabbitMQConsumer()){
        $this->consumer = $consumer;
        $this->producer = $producer;
    }

    public function consume()
    {
        $this->consumer->consume();
    }

    public function produce(mixed $message)
    {
        //add some logic 
        //the produser should check if there is a producer or not before produsing and if he can produce
        //then he will pubish to redis that he is alive until he finish and update a variable showing his progress if he [produce it to the queue]
        $this->producer->produce($message);
    }
}
