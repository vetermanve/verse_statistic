<?php


namespace Verse\Statistic\Configuration\Grouping;


use Verse\Statistic\Aggregate\EventsContainer;

abstract class AbstractGrouping
{
    /**
     * @var EventsContainer
     */
    protected $eventsContainer;

    protected $eventsIdx = [];

    
    abstract public function getStatRecords() : array ;
    abstract public function getGroupType() : int ;
    abstract public function getGroupName() : string ;
    
    /**
     * @return EventsContainer
     */
    public function getEventsContainer() : EventsContainer
    {
        return $this->eventsContainer;
    }

    /**
     * @param EventsContainer $eventsContainer
     */
    public function setEventsContainer(EventsContainer $eventsContainer)
    {
        $this->eventsContainer = $eventsContainer;
    }

    /**
     * @return array
     */
    public function getEventsIdx() : array
    {
        return $this->eventsIdx;
    }

    /**
     * @param array $eventsIdx
     */
    public function setEventsIdx(array $eventsIdx)
    {
        $this->eventsIdx = $eventsIdx;
    }
}