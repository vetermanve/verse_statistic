<?php


namespace Verse\Statistic\Storage\Unique;


use Verse\Statistic\Core\Model\StatRecord;

class InMemoryUniqueStorage implements UniqueStorageInterface
{
    protected $store = [];

    public function checkRecordsUnique($statsRecords)
    {
        $results = [];

        foreach (array_keys($statsRecords) as $uniqIdKey) {
            $results[$uniqIdKey] = !isset($this->store[$uniqIdKey]);
        }

        return $results;
    }

    public function storeRecordsUnique($statsRecords)
    {
        $index = array_combine(array_keys($statsRecords), array_fill(0, count($statsRecords), true));

        $this->store = $index + $this->store;

        return true;
    }

    public function findRecords($eventIds, $timeFrom, $timeTo, $timeScale, $groupType = 0, array $groupIds = [], $scopeId = 0, $limit = 100000) : array 
    {
        $checks = [
            function ($rec) use ($eventIds) {
                return \in_array($rec[StatRecord::EVENT_ID], $eventIds, true);
            },
            function ($rec) use ($timeFrom) {
                return $rec[StatRecord::TIME] >= $timeFrom;
            },
            function ($rec) use ($timeTo) {
                return $rec[StatRecord::TIME] <= $timeTo;
            },
            function ($rec) use ($timeScale) {
                return $rec[StatRecord::TIME_SCALE] === $timeScale;
            },
            function ($rec) use ($groupType) {
                return $rec[StatRecord::GROUP_TYPE] === $groupType;
            },
            function ($rec) use ($groupIds) {
                return \in_array($rec[StatRecord::GROUP_ID], $groupIds, true);
            },
            function ($rec) use ($scopeId) {
                return $rec[StatRecord::SCOPE_ID] === $scopeId;
            },
        ];

        $results = [];
        foreach ($this->store as $id => $rec) {
            $filtered = false;
            foreach ($checks as $checkFunction) {
                if (!$checkFunction($rec)) {
                    $filtered = true;
                    break;
                }
            }
            if (!$filtered) {
                $results[$id] = $rec;
            }
        }
        
        return $results;
    }
}