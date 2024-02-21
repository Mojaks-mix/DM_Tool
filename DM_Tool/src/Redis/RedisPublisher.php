<?php

namespace src\Redis;

use src\Contracts\PublisherInterface;

class RedisPublisher implements PublisherInterface
{
    private $conn;

    public function __construct()
    {
        $this->conn = RedisSingleton::getInstance()->getConnection();
    }

    public function publishToAllChannels($message)
    {
        // Get the list of all channels
        $channels = $this->conn->pubsub('channels', '*');

        // Publish message to each channel
        foreach ($channels as $channel) 
        {
            $this->publishToChannel($channel, $message);
        }
    }

    public function publishToChannel($channel, $message)
    {
        // Publish message to a specific channel
        $this->conn->publish($channel, $message);
    }
}
