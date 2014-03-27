<?php
require_once __DIR__ . "/vendor/autoload.php";

$mongo = new MongoClient();
$mongoDB = $mongo->selectDB("test");

$manager = new \MeadSteve\MonWorkGo\Manager($mongoDB);


$manager->getQueue("mainQueue")
    ->clearCompletedWork()      // Do a bit of house keeping
    ->addWork(["jobOne", 34])
    ->addWork(["jobTwo", 5, 7])
    ->addWork(["jobThree", 8])
    ->addWork(["jobFour", "bob"])
    ->addWork(["jobFive", "dave"]);
