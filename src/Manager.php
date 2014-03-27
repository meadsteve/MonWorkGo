<?php

namespace MeadSteve\MonWorkGo;


use Psr\Log\LoggerInterface;

class Manager
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

    public function __construct(\MongoDB $mongoDB, $collectionPrefix = "", LoggerInterface $logger = null)
    {
        $this->mongoDB = $mongoDB;
        $this->logger = $logger;
        $this->collectionPrefix = $collectionPrefix;
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
