<?php


namespace Verse\Statistic\Storage\Unique;


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
}