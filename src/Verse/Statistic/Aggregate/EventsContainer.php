<?php


namespace Verse\Statistic\Aggregate;


class EventsContainer
{
    /**
     * Array of all events
     * 
     * @var array
     */
    public $events = [];
    
    /**
     * Event Names in index and hashes in value
     * @var array
     */
    public $eventNamesHashes = [];

    /**
     * Container Action reports;
     *
     * @var array
     */
    public $reports = [];

    public function eventsCount () : int 
    {
        return \count($this->events);
    }
    
    public function addEvents ($events) : void 
    {
        $this->events = array_merge($this->events, $events);
    }
}