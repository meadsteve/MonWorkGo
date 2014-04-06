<?php

require_once __DIR__ . "/vendor/autoload.php";

$mongo = new MongoClient();
$mongoDB = $mongo->selectDB("test");

class EchoLogger extends \Psr\Log\AbstractLogger
{
    public function log($level, $message, array $context = array())
    {
        echo $message . PHP_EOL;
    }
}
$logger = new EchoLogger();

$manager = (new \MeadSteve\MonWorkGo\Manager($mongoDB))->setLogger($logger);

$manager->createWorker(
    "mainQueue",
    function ($payload, \Psr\Log\LoggerInterface $log) {
        $log->info("Hello. I'm working on: " . var_export($payload, true));
        return \MeadSteve\MonWorkGo\Worker::WORK_RESPONSE_SUCCESS;
    }
)->start();
