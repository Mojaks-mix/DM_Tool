<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor\autoload.php';

use src\Redis\Subscriber;

$subscriber = new Subscriber();
$subscriber->subscribeToChannel("s1",$callback = function ($redis, $channel, $message) {
    echo "Received message on channel $channel: $message\n";
});