<?php

namespace MeadSteve\MonWorkGo\Tests;

use MeadSteve\MonWorkGo\Queue;
use \Mockery\MockInterface;
use \Mockery as m;

class QueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Queue
     */
    protected $testedQueue;

    /**
     * @var MockInterface
     */
    protected $mockCollection;

    protected function tearDown()
    {
        m::close();
    }

    protected function setUp()
    {
        $this->mockCollection = m::mock('\MongoCollection');
        $this->testedQueue = new Queue($this->mockCollection);
    }

    public function testGetWorkCallFindAndModifyOnCollection()
    {
        // Should look for new work
        $expectedQuery = ['status' => 'new'];

        // Should mark the work as in progress
        $expectedUpdate = m::on(function ($actualSetArgument) {
            return $actualSetArgument['$set']['status'] === 'processing';
        });

        $this->mockCollection->shouldReceive("findAndModify")
            ->with($expectedQuery, $expectedUpdate, m::any(), m::any())
            ->andReturnNull()
            ->once();

        $this->testedQueue->getWork();
    }

    public function testGetWorkReturnsWorkUnit()
    {
        $this->mockCollection->shouldReceive("findAndModify")
            ->andReturn(['_id' => new \MongoId("507f191e810c19729de860ea"), 'payload' => []]);

        $work = $this->testedQueue->getWork();
        $this->assertInstanceOf('\MeadSteve\MonWorkGo\WorkUnit', $work);
    }

    public function testGetWorkPopulatesWorkUnitCorrectly()
    {
        $expectedID = new \MongoId("507f191e810c19729de860ea");
        $expectedPayload = ['food', 'bard'];

        $this->mockCollection->shouldReceive("findAndModify")
            ->andReturn(['_id' => $expectedID, 'payload' => $expectedPayload]);

        $work = $this->testedQueue->getWork();

        $this->assertAttributeEquals($expectedID, 'identifier', $work);
        $this->assertAttributeEquals($expectedPayload, 'payload', $work);
    }

    public function testAddWorkInsertsToCollection()
    {
        $priority = 1;
        $payload = ["food", "bard"];

        // Should receive the payload and priority and be new status
        $expectedInsert = [
            'status' => 'new',
            'payload' => $payload,
            'priority' => $priority
        ];

        // We aren't expecting to wait for a write.
        $expectedOptions = ['w' => 0];

        $this->mockCollection->shouldReceive('insert')
            ->with($expectedInsert, $expectedOptions);

        $this->testedQueue->addWork($payload, $priority);
    }
}
