<?php

namespace MeadSteve\MonWorkGo;

class Queue
{
    const STATUS_NEW = 'new';
    const STATUS_PROCESSING = 'processing';

    /**
     * @var \MongoCollection
     */
    protected $collection;

    public function __construct(\MongoCollection $collection)
    {
        $this->collection = $collection;
    }

    public function addWork($payload, $priority = 0)
    {
        $this->collection->insert(
            [
                'status' => self::STATUS_NEW,
                'payload' => $payload,
                'priority' => $priority
            ],
            ['w' => 0]
        );

        return $this;
    }

    public function getWork()
    {
        return $this->collection->findAndModify(
            ['status' => self::STATUS_NEW],
            ['$set' => ['status' => self::STATUS_PROCESSING, "started" => new \MongoDate()]],
            null,
            [
                "sort" => array("priority" => -1),
            ]
        );
    }
}
