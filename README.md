MonWorkGo
=========

Simple work queue built on top of mongodb. A single collection in mongoDB is used to represent one work queue. A json payload is used to desribe the work which is then processed by a supplied callback.

## Installation
The easiest way to install this library is using composer. In your project's composer.json file add:

    {
        "require": {
            "mead-steve/mon-work-go": "1.1.*"
        }
    }
Then run composer update.

## Queueing up work
A mixed array of data describing your work can get queued for processing. This will be quick so you can respond to your user like a hero. This will create a collection called mainQueue in your mongoDB (if it doesn't already exist).

```php
$manager = new \MeadSteve\MonWorkGo\Manager($mongoDB);

$manager->getQueue("mainQueue")
    ->clearCompletedWork()      // Do a bit of house keeping
    ->addWork(["jobOne", 34])
    ->addWork([5, 7])
    ->addWork([8])
    ->addWork(["jobFour", "mead"])
    ->addWork(["jobFive", "beer"]);
```

## Processing Work
Once your data has been nicely queued you can spend a bit more time processing it. To be honest you'll probably want to do a little more than echoing it but this should give you the idea. This script will run indefinitely and there's nothing stopping you having it running on multiple instances to share the load.

```php
$manager = new \MeadSteve\MonWorkGo\Manager($mongoDB);

$manager->createWorker(
    "mainQueue",
    function ($payload) {
        echo "Hello. I'm working on: " . var_export($payload, true);
        return \MeadSteve\MonWorkGo\Worker::WORK_RESPONSE_SUCCESS;
    }
)->start();
```

## Reporting on the progress of the work
The worker object can also be provided with a psr logger. In addition to sending debug messages to this logger the work function also gets passed the logger as the second argument so can report on its own progress.

```php
$manager = (new \MeadSteve\MonWorkGo\Manager($mongoDB))->setLogger($logger);

$manager->createWorker(
    "mainQueue",
    function ($payload, \Psr\Log\LoggerInterface $log) {
        $log->info("Hello logger");
        $log->info("I should probably do some work");
        return \MeadSteve\MonWorkGo\Worker::WORK_RESPONSE_SUCCESS;
    }
)->start();
```
