<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor\autoload.php';

use src\Core\EmailTask;
use src\Redis\RedisPublisher;
use src\Redis\RedisSubscriber;
use src\RabbitMQ\DirectRabbitMQConsumer;
use src\RabbitMQ\DirectRabbitMQProducer;

//the produser should check if there is a producer or not before produsing and if he can produce
//then he will pubish to redis that he is alive until he finish and update a variable showing his progress if he [produce it to the queue]

$mailTaskProduser = new EmailTask(new DirectRabbitMQProducer(),new DirectRabbitMQConsumer());

for($count = 0; $count < 5;){
    $mailTaskProduser->produce(json_encode(['id' => ++$count]));
    sleep(1);
}