<?php


namespace MeadSteve\MonWorkGo;


use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Worker
{
    const WORK_RESPONSE_HALT_PROCESSING = 2;
    const WORK_RESPONSE_FAILED = 1;
    const WORK_RESPONSE_SUCCESS = 0;

    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var callable
     */
    protected $workFunction;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(Queue $queue, callable $workFunction, LoggerInterface $logger = null)
    {
        $this->queue = $queue;
        $this->workFunction = $workFunction;
        $this->logger = $logger ?: new NullLogger();
    }

    public function start()
    {
        $running = true;
        while ($running) {
            $work = $this->queue->getWork();
            if ($work !== null) {
                $running = $this->doWork($work);
            } else {
                sleep(1000);
            }
        }

        return $this;
    }

    /**
     * Calls the workfunction with the payload. Returns false if the work should stop.
     * @param $work
     * @return bool
     */
    protected function doWork(WorkUnit $work)
    {
        $call = $this->workFunction;
        $response = $call($work->payload, $this->logger);

        if ($response & self::WORK_RESPONSE_FAILED) {
            $this->queue->markWorkAsFailed($work);
        } else {
            $this->queue->markWorkAsComplete($work);
        }

        return ($response & self::WORK_RESPONSE_HALT_PROCESSING) !== 0;
    }
}
