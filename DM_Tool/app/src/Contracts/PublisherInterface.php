<?php

namespace src\Contracts;

interface PublisherInterface
{
    public function publishToChannel($channel, $message);
    public function publishToAllChannels($message);
}
