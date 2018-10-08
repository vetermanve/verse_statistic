<?php


namespace Verse\Statistic\Storage\Records;


interface StatRecordsStorageInterface
{
    public function addRecords ($records);
    public function findRecords ($eventIds, $timeFrom, $timeTo, $timeScale, $groupType = 0, $groupIds = [], $scopeId = 0, $limit = 100000);
}