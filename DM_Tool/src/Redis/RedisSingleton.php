<?php

namespace src\Redis;
use Redis;

class RedisSingleton {
    private static $instance = null;
    private $redis;

    private function __construct() {
        // Connect to Redis server
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new RedisSingleton();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->redis;
    }

    public function __destruct()
    {
        $this->redis->close();
    }
}