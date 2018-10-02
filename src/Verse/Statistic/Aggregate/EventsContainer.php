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
     * Array of events split by global scope  
     * 
     * @var array
     */
    public $eventsByScope = [];

    /**
     * Array of events split by unique users
     * @todo check usage
     * 
     * @var array
     */
    public $userIdsByCompany = [];
    
    
    public function eventsCount () : int 
    {
        return \count($this->events);
    }
    
    public function addEvents ($events) : void 
    {
        $this->events = array_merge($this->events, $events);
    }
    
    public function setEvents ($events) : void
    {
        $this->events = $events;
    }
    
    public function getEventsByScopeCount()  : int
    {
        return \count($this->eventsByScope);
    }
    
    public function getScopesIds()
    {
        return array_keys($this->eventsByScope);
    }
    
}