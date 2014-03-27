<?php


namespace MeadSteve\MonWorkGo;


class WorkUnit
{
    public $payload;
    public $identifier;

    public function __construct(\MongoId $identifier, $payload)
    {
        $this->payload = $payload;
        $this->identifier = $identifier;
    }
}
