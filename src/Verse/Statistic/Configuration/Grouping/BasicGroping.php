<?php


namespace Verse\Statistic\Configuration\Grouping;


use Verse\Statistic\Core\Model\Event;
use Verse\Statistic\Core\Model\StatRecord;
use Verse\Statistic\Core\Model\TimeScale;

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
                StatRecord::SCOPE_ID   => $event[Event::SCOPE_ID],
                StatRecord::GROUP_TYPE => $groupType,
                StatRecord::GROUP_ID   => 0,
                StatRecord::UNIQUE_ID  => $event[Event::UNIQUE_ID],
                StatRecord::TIME       => $event[Event::TIME],
                StatRecord::TIME_SCALE => TimeScale::RAW,
                StatRecord::COUNT      => $event[Event::COUNT],
                StatRecord::COUNT_UNQ  => 0,
            ];
        }

        return $records;
    }

    public function getGroupType() : int
    {
        return self::TYPE;
    }

    public function getGroupName() : string
    {
        return 'All';
    }
}