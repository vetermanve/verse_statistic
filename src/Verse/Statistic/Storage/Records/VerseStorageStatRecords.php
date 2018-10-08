<?php


namespace Verse\Statistic\Storage\Records;


use Verse\Statistic\Core\Model\StatRecord;
use Verse\Storage\Data\JBaseDataAdapter;
use Verse\Storage\SimpleStorage;
use Verse\Storage\Spec\Compare;
use Verse\Storage\StorageContext;
use Verse\Storage\StorageDependency;

class VerseStorageStatRecords extends SimpleStorage implements StatRecordsStorageInterface
{
    protected $dataRootPath = '/tmp/';

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

    public function loadConfig()
    {
        $adapter = new JBaseDataAdapter();
        $adapter->setDataRoot($this->dataRootPath);
        $adapter->setDatabase('statistic');
        $adapter->setResource('stats');

        $this->diContainer->setModule(StorageDependency::DATA_ADAPTER, $adapter);
    }

    public function customizeDi(StorageDependency $container, StorageContext $context)
    {
        
    }

    public function findRecords($eventIds, $timeFrom, $timeTo, $timeScale, $groupType = 0, $groupIds = [], $scopeId = 0, $limit = 100000)
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

    /**
     * @return mixed
     */
    public function getDataRootPath()
    {
        return $this->dataRootPath;
    }

    /**
     * @param mixed $dataRootPath
     */
    public function setDataRootPath($dataRootPath)
    {
        $this->dataRootPath = $dataRootPath;
    }
}