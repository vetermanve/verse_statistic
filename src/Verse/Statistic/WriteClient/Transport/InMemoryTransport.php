<?php


namespace Verse\Statistic\WriteClient\Transport;


class InMemoryTransport implements StatisticWriteClientTransportInterface
{
    protected $iterator = 0;
    protected $events = [];
    protected $limit = 1000;
    
    public function send(string $payload) : bool 
    {
        $this->events[$this->iterator++] = $payload;
        return true;
    }

    /**
     * @return int
     */
    public function getIterator() : int
    {
        return $this->iterator;
    }

    /**
     * @return array
     */
    public function getEvents() : array
    {
        return $this->events;
    }

    /**
     * @return int
     */
    public function getLimit() : int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit)
    {
        $this->limit = $limit;
    }
}