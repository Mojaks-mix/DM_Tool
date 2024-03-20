<?php

namespace src\Redis;

use src\Contracts\SubscriberInterface;

class RedisSubscriber implements SubscriberInterface
{
    private $conn;
    private $channel;

    public function __construct()
    {
        $this->conn     = RedisSingleton::getInstance()->getConnection();
        $this->channel  = [];
    }

    public function subscribeToChannel(string $channel, callable $MessageFormatte)
    {
        // Subscribe to channel with callback function
        $this->channel[]    = $channel;
        $this->conn->subscribe([$channel], $MessageFormatte);
    }

    public function __destruct()
    {
        $this->conn->unsubscribe($this->channel);
    }
}
