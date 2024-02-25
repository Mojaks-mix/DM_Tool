<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor\autoload.php';

use src\Core\EmailTask;
use src\Redis\RedisPublisher;
use src\Redis\RedisSubscriber;

//the produser should check if there is a producer or not before produsing and if he can produce
//then he will pubish to redis that he is alive until he finish and update a variable showing his progress if he [produce it to the queue]

$mailTaskProduser = new EmailTask();

$count = 0;
while(true){
    $mailTaskProduser->produce(json_encode(['id' => ++$count]));
    sleep(3);
}