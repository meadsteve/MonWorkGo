<?php

namespace MeadSteve\MonWorkGo;

class Queue
{
    const STATUS_NEW = 'new';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETE = 'complete';
    const STATUS_FAILED = 'failed';

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

    /**
     * @return WorkUnit|null
     */
    public function getWork()
    {
        $workObject = $this->collection->findAndModify(
            ['status' => self::STATUS_NEW],
            ['$set' => ['status' => self::STATUS_PROCESSING, "started" => new \MongoDate()]],
            null,
            [
                "sort" => array("priority" => -1),
            ]
        );
        if (isset($workObject['payload'])) {
            return new WorkUnit($workObject['_id'], $workObject['payload']);
        } else {
            return null;
        }
    }

    public function markWorkAsComplete(WorkUnit $work)
    {
        $this->collection->update(
            ['_id'=> $work->identifier],
            ['$set' => ['status' => self::STATUS_COMPLETE, "ended" => new \MongoDate()]],
            ['w' => 0]
        );
        return $this;
    }

    public function markWorkAsFailed(WorkUnit $work)
    {
        $this->collection->update(
            ['_id'=> $work->identifier],
            ['$set' => ['status' => self::STATUS_FAILED, "ended" => new \MongoDate()]],
            ['w' => 0]
        );
        return $this;
    }
}
