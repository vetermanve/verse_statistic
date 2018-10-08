<?php


namespace Verse\Statistic\Storage\Unique;


use Verse\Statistic\Core\Model\StatRecord;
use Verse\Statistic\Storage\Unique\UniqueStorageInterface;
use Verse\Storage\Data\JBaseDataAdapter;
use Verse\Storage\SimpleStorage;
use Verse\Storage\Spec\Compare;
use Verse\Storage\StorageContext;
use Verse\Storage\StorageDependency;

class VerseStorageUnique extends SimpleStorage implements UniqueStorageInterface
{
    const DATA_ROOT_PATH = 'data-root-path';
    const DATA_DATABASE  = 'data-database';
    const DATA_TABLE     = 'data-table';

    /**
     * Storage setup configuration
     */
    public function loadConfig()
    {

    }

    public function customizeDi(StorageDependency $container, StorageContext $context)
    {
        $adapter = new JBaseDataAdapter();
        $adapter->setDataRoot($this->context->get(self::DATA_ROOT_PATH, '/tmp'));
        $adapter->setDatabase($this->context->get(self::DATA_DATABASE, 'statistic'));
        $adapter->setResource($this->context->get(self::DATA_TABLE, 'unique'));

        $this->diContainer->setModule(StorageDependency::DATA_ADAPTER, $adapter);
    }

    public function addRecords($records)
    {
        $keys = array_keys($records);
        
        $oldRecords = $this->read()->mGet($keys, __METHOD__);
        $oldRecords = array_filter($oldRecords);
        $updatedRecords = [];
        
        foreach ($oldRecords as $key => $record) {
            $updatedRecords[$key] = [
                StatRecord::COUNT => $oldRecords[$key][StatRecord::COUNT] + $records[$key][StatRecord::COUNT], 
                StatRecord::COUNT_UNQ => $oldRecords[$key][StatRecord::COUNT_UNQ] + $records[$key][StatRecord::COUNT_UNQ], 
            ] + $record;
        }
        
        $newRecords = array_diff_key($records, $oldRecords);
        
        $writeResultsNew = $this->write()->insertBatch($newRecords, __METHOD__);
        $writeResultsUpdate = $this->write()->updateBatch($updatedRecords, __METHOD__);

        $writeResultsNewSuccess = array_filter($writeResultsNew);
        $writeResultsUpdateSuccess = array_filter($writeResultsUpdate);
        
        return \count($writeResultsNewSuccess) + \count($writeResultsUpdateSuccess) === \count($records);
    }

    public function findRecords($eventIds, $timeFrom, $timeTo, $timeScale, $groupType = 0, array $groupIds = [], $scopeId = 0, $limit = 100000) : array 
    {
        $filter = []; 
        $filter[] = [StatRecord::SCOPE_ID, Compare::EQ, $scopeId];
        $filter[] = [StatRecord::EVENT_ID, Compare::IN, $eventIds];
        $filter[] = [StatRecord::TIME, Compare::GRATER_OR_EQ, $timeFrom];
        $filter[] = [StatRecord::TIME, Compare::LESS_OR_EQ, $timeTo];
        $filter[] = [StatRecord::TIME_SCALE, Compare::EQ, $timeScale];
        $filter[] = [StatRecord::GROUP_TYPE, Compare::EQ, $groupType];
        
        if ($groupIds) {
            $filter[] = [StatRecord::GROUP_ID, Compare::IN, $groupIds];
        }
        
        return $this->search()->find($filter, $limit, __METHOD__);
    }
    
    public function checkRecordsUnique($statsRecords) : array 
    {
        $uniqIdsHashIndex = [];
        foreach ($statsRecords as $id => $statsRecord) {
            $keyHash = md5(StatRecord::getRecordUniqId($statsRecord));
            $uniqIdsHashIndex[$keyHash] = $id;
        }

        $presentUnique = $this->read()->mGet(array_keys($uniqIdsHashIndex), __METHOD__);
        
        $results = [];
        foreach ($uniqIdsHashIndex as $keyHash => $id) {
            $results[$id] = !isset($presentUnique[$keyHash]);       
        }
        
        return $results;
    }

    public function storeRecordsUnique($statsRecords)
    {
        $insertBind = [];
        foreach ($statsRecords as $id => $statsRecord) {
            if ($statsRecord[StatRecord::COUNT_UNQ] !== 1) {
                continue;
            }
            
            $keyHash = md5(StatRecord::getRecordUniqId($statsRecord));
            
            unset(
                $statsRecord[StatRecord::COUNT],
                $statsRecord[StatRecord::COUNT_UNQ]
            );
            
            $insertBind[$keyHash] = $statsRecord;
        }

        $insertResult = $this->write()->insertBatch($insertBind, __METHOD__);

        $successfullyInserted = \count(array_filter($insertResult));
        $writeBindCount = \count($insertBind);
        
        if ($successfullyInserted !== $writeBindCount) {
            \trigger_error("Uniq records insert troubles, possible race condition on insert, comes '$writeBindCount' but inserted '$successfullyInserted'", E_USER_WARNING);
        }

        return true;
    }
}