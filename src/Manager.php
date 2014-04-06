<?php

namespace MeadSteve\MonWorkGo;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class Manager implements LoggerAwareInterface
{
    /**
     * @var \MongoDB
     */
    protected $mongoDB;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $collectionPrefix;

    public function __construct(\MongoDB $mongoDB, $collectionPrefix = "")
    {
        $this->mongoDB = $mongoDB;
        $this->collectionPrefix = $collectionPrefix;
    }

    /**
     * Sets a logger instance on the object.
     *
     * This will be passed to all workers
     *
     * @param LoggerInterface $logger
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    /**
     * @param string $queueName
     * @return Queue
     */
    public function getQueue($queueName)
    {
        $collection = $this->getOrCreateCollection($queueName);
        return new Queue($collection);
    }

    /**
     * @param $queueName
     * @param $workFunction
     * @return Worker
     */
    public function createWorker($queueName, $workFunction)
    {
        return new Worker(
            $this->getQueue($queueName),
            $workFunction,
            $this->logger
        );
    }

    /**
     * @param string $queueName
     * @return \MongoCollection
     */
    protected function getOrCreateCollection($queueName)
    {
        $collectionName = $this->collectionPrefix . $queueName;
        if (!isset($this->mongoDB->getCollectionNames()[$collectionName])) {
            $this->mongoDB->createCollection($collectionName);
        }
        $collection = $this->mongoDB->{$collectionName};
        return $collection;
    }
}
