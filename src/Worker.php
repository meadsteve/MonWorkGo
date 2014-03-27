<?php


namespace MeadSteve\MonWorkGo;


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

    public function __construct(Queue $queue, callable $workFunction)
    {
        $this->queue = $queue;
        $this->workFunction = $workFunction;
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
        return ($call($payload) !== false);
    }
}
