<?php

namespace src\Redis;

use src\Contracts\PublisherInterface;

class RedisPublisher implements PublisherInterface
{
    private $conn;
    private $lockKey;

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

    public function setLock(string $lockKey, int $expiration = 5): bool
    {
        $this->lockKey = $lockKey;
        return $this->conn->set($lockKey, 'locked', ['NX', 'EX' => $expiration]);
    }
}
