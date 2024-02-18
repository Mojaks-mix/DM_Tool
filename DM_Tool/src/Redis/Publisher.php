<?php

namespace src\Redis;

class Publisher {
    private $redis;

    public function __construct() {
        $this->redis = RedisSingleton::getInstance()->getConnection();
    }

    public function publishToAllChannels($message) {
        // Get the list of all channels
        $channels = $this->redis->pubsub('channels', '*');

        // Publish message to each channel
        foreach ($channels as $channel) {
            $this->publishToChannel($channel, $message);
        }
    }

    public function publishToChannel($channel, $message) {
        // Publish message to a specific channel
        $this->redis->publish($channel, $message);
    }
}
