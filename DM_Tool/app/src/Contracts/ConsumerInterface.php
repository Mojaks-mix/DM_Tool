<?php

namespace src\Contracts;

interface ConsumerInterface
{
    public function consume(callable $msgAction);
}
