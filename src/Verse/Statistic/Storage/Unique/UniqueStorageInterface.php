<?php


namespace Verse\Statistic\Storage\Unique;


interface UniqueStorageInterface
{
    public function checkRecordsUnique($statsRecords);
    public function storeRecordsUnique($statsRecords);
}