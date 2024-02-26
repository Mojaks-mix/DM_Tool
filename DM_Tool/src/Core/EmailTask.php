<?php

namespace src\Core;

use src\Contracts\ConsumerInterface;
use src\Contracts\ProducerInterface;
use src\Contracts\PublisherInterface;
use src\Contracts\SubscriberInterface;
use src\RabbitMQ\BasicRabbitMQConsumer;
use src\RabbitMQ\BasicRabbitMQProducer;
use src\Redis\RedisPublisher;
use src\Redis\RedisSubscriber;

class EmailTask
{
    public function __construct(private ProducerInterface $producer = new BasicRabbitMQProducer(),
        private ConsumerInterface $consumer = new BasicRabbitMQConsumer(),
        private PublisherInterface $pub = new RedisPublisher(),
        private SubscriberInterface $sub = new RedisSubscriber()) {
        $this->consumer = $consumer;
        $this->producer = $producer;
    }

    public function consume()
    {
        $this->consumer->consume();
    }

    public function produce(mixed $message)
    {
        //the produser should check if there is a producer or not before produsing and if he can produce
        //then he will pubish to redis that he is alive until he finish and update a variable showing his progress if he [produce it to the queue]

        $timeout = 3;
        $lastProducerBeatTime = 3;
        
        $this->sub->subscribeToChannel('producer_heartbeat', function ($redis, $channel, $msg) use (&$lastProducerBeatTime, $timeout) {
            echo time();
            if ($msg === 'alive') {
                echo "still waiting the other producer to finish, last heartbeat message was at $lastProducerBeatTime.\n";
                $lastProducerBeatTime = time();
            }
        });
        
        while (true) {
            if (time() - $lastProducerBeatTime > $timeout) {
                $this->pub->publishToChannel('producer_heartbeat', 'alive');
                $this->producer->produce($message);
                exit;
            }
        }
    }
}
