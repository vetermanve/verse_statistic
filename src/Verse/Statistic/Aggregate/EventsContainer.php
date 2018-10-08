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
}