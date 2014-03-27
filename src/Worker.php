<?php


namespace MeadSteve\MonWorkGo;


use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Worker
{
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
            $payload = $this->queue->getWork();
            if ($payload !== null) {
                $running = $this->doWork($payload);
            } else {
                sleep(1000);
            }
        }

        return $this;
    }

    /**
     * Calls the workfunction with the payload. Returns false if the work should stop.
     * @param $payload
     * @return bool
     */
    protected function doWork($payload)
    {
        $call = $this->workFunction;
        return ($call($payload, $this->logger) !== false);
    }
}
