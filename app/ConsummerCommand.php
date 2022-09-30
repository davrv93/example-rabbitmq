<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use App\Http\Data\Jobs\Main;


class ConsumerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:consumers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'RabbitMQ Consumer';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        echo 'envorinment: '.env('AMQP','');
        $connection = new AMQPStreamConnection(env('AMQP',''), 5672, 'tilamb', 'lamBAcademic2020##');
        $channel = $connection->channel();

        //MARCAMOS DURABLE
        $channel->queue_declare(
            'task_queue',
            false,
            true,// RABBIT NO BORRARÁ LOS MENSAJES SI TIENE PROBLEMAS
            false,
            false
        );

        echo " [*] Waiting for messages. To exit press CTRL+C\n";

        $callback = function ($msg) {
            echo ' [x] Received ', $msg->body, "\n";
            //AVISAMOS QUE HEMOS RECIBIDO EL MENSAJE
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        //SOLO SE VA A DEJAR UN MENSAJE A UN CONSUMER A LA VEZ
        //nO LE ENVÍA UN NUEVO MENSAJE HASTA QUE NO PROCESE EL ANTERIOR
        $channel->basic_qos(null, 1, null);

        //AVISAR A RABBIT QUE SE HA CONSUMIDO EL MENSAJE
        $channel->basic_consume(
            'task_queue',
            '',
            false,
            false,
            false,
            false,
            $callback
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();


        return;
    }
}
