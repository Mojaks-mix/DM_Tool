<?php

namespace src\Core;

use src\Contracts\ConsumerInterface;
use src\Contracts\ProducerInterface;
use src\Contracts\PublisherInterface;
use src\Contracts\SubscriberInterface;
use src\RabbitMQ\DirectRabbitMQConsumer;
use src\RabbitMQ\DirectRabbitMQProducer;
use src\Redis\RedisPublisher;
use src\Redis\RedisSubscriber;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;

class EmailTask
{
    public function __construct(private ProducerInterface $producer = new DirectRabbitMQProducer(),
        private ConsumerInterface $consumer = new DirectRabbitMQConsumer(),
        private PublisherInterface $pub     = new RedisPublisher(),
        private SubscriberInterface $sub    = new RedisSubscriber()) {
        $this->consumer = $consumer;
        $this->producer = $producer;
    }

    public function consume()
    {
        $this->consumer->consume(function ($msg) {
                $message = $msg->getBody();
                if ($this->consumer->isJson($message)) {
                    $message = json_decode($message);
                }
                //echo ' [x] Received ', var_dump($message), "\n";
                $email = (new Email())
                ->from("test@mytest.com")
                ->to("tareqwaleed1996@gmail.com")
                ->subject("Test")
                ->text($message);

                //$dsn = 'gmail+smtp://google-account:App-Key@default';
                $dsn = 'smtp://mailhog:1025';
                $transport = Transport::fromDsn($dsn);

                $mailer = new Mailer($transport);
                $mailer->send($email);

                sleep(1);
                //echo " [x] Done\n";
                $msg->ack(); //important
                sleep(4);
            }
        );
    }

    public function produce(mixed $message)
    {
        if ($this->pub->setLock('lock:heartbeat_channel', 3)) {
            // Lock acquired, publish the message
            $this->pub->publishToChannel('heartbeat_channel', 'alive');
            $this->producer->produce($message);
            echo "Email added to queue successfully\n";

            // Release the lock (optional)
            //$redis->del($lockKey);
        } else {
            echo "Failed to acquire lock. Another producer is already running.\n";
            }
        }
}
