<?php

namespace src\Redis;

class Subscriber {
    private $redis;
    private $channel;

    public function __construct() {
        $this->redis = RedisSingleton::getInstance()->getConnection();
        $this->channel = [];
    }

    public function subscribeToChannel(string $channel, callable $MessageFormatte) {       
        // Subscribe to channel with callback function
        $this->channel[] = $channel;
        $this->redis->subscribe([$channel], $MessageFormatte);
    }

    public function __destruct()
    {
        $this->redis->unsubscribe($this->channel);
    }
}