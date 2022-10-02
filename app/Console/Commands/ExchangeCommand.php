<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;


class ExchangeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:exchange';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sending message through an exchange';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        
        $channel = $connection->channel();

        $data = 'hello world';

        $msg = new AMQPMessage(
            $data,
            array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
        );

        $channel->basic_publish($msg, 'test_exchange', 'test_key');

        echo ' [x] Sent ', $data, "\n";

        $channel->close();

        $connection->close();

        return Command::SUCCESS;
    }
}
