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
        $this->logger->debug("Worker starting");

        while ($running) {
            $work = $this->queue->getWork();
            if ($work !== null) {
                $running = $this->tryAndDoWork($work);
            } else {
                $this->logger->debug("Sleeping as no work found");
                sleep(10);
            }
        }

        $this->logger->debug("Worker stopping");
        return $this;
    }

    /**
     * @param $work
     * @return bool
     */
    protected function tryAndDoWork($work)
    {
        $running = true;
        try {
            $stringId = (string)$work->identifier;
            $this->logger->debug("Starting Unit of work: " . $stringId);
            $running = $this->doWork($work);
            $this->logger->debug("Finished Unit of work: " . $stringId);
        } catch (\Exception $error) {
            $this->logger->error(
                "Work id $stringId caused error: " . $error->getMessage(),
                ['exception' => $error]
            );
            $this->queue->markWorkAsFailed($work);
        }
        return $running;
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
            $this->logger->debug("Work has failed");
        } else {
            $this->queue->markWorkAsComplete($work);
            $this->logger->debug("Work has succeeded");
        }

        return !$this->shouldHalt($response);
    }

    protected function shouldHalt($response)
    {
        $halt = ($response & self::WORK_RESPONSE_HALT_PROCESSING) !== 0;
        if ($halt) {
            $this->logger->debug("Work has requested worker terminates");
        }
        return $halt;
    }
}
