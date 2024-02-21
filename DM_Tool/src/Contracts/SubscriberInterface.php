<?php

namespace src\Contracts;

interface SubscriberInterface
{
    public function subscribeToChannel(string $channel, callable $MessageFormatte);
}
