<?php


namespace Verse\Statistic\Storage\Unique;


interface UniqueStorageInterface
{
    public function checkRecordsUnique($statsRecords);
    public function storeRecordsUnique($statsRecords);
    public function findRecords ($eventIds, $timeFrom, $timeTo, $timeScale, $groupType = 0, array $groupIds = [], $scopeId = 0, $limit = 100000) : array ;
}