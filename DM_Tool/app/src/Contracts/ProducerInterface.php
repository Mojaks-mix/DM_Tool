<?php

namespace src\Contracts;

interface ProducerInterface
{
    public function produce(mixed $data);
}
