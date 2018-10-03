<?php


namespace Verse\Statistic\Configuration\Grouping;


use Verse\Statistic\Core\Event;
use Verse\Statistic\Core\Model\StatRecord;

class BasicGroping extends AbstractGrouping
{
    const TYPE = 1;
    
    public function getStatRecords() : array
    {
        $records = [];
        $groupType = $this->getGroupType();
        $eventContainer = $this->eventsContainer;

        foreach ($eventContainer->events as $event) {
            $records[] = [
                StatRecord::EVENT_ID   => $eventContainer->eventNamesHashes[$event[Event::NAME]] ?? crc32($event[Event::NAME]),
                StatRecord::COUNT      => $event[Event::COUNT],
                StatRecord::TIME_RAW   => $event[Event::TIME],
                StatRecord::SCOPE_ID   => $event[Event::SCOPE_ID],
                StatRecord::COUNT_UNQ  => 0,
                StatRecord::GROUP_TYPE => $groupType,
                StatRecord::GROUP_ID   => 0,
            ];
        }

        return $records;
    }

    public function getGroupType() : int
    {
        return self::TYPE;
    }
    
    public function getGroupName () : string 
    {
        return 'All';
    }
}