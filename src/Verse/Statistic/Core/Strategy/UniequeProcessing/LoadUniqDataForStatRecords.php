<?php


namespace Verse\Statistic\Core\Strategy\UniequeProcessing;


use Verse\Modular\ModularStrategyInterface;
use Verse\Statistic\Core\Model\StatRecord;
use Verse\Statistic\Core\StatsModuleProto;
use Verse\Statistic\Storage\Unique\InMemoryUniqueStorage;
use Verse\Statistic\Storage\Unique\UniqueStorageInterface;

class LoadUniqDataForStatRecords extends StatsModuleProto implements ModularStrategyInterface
{

    public function prepare()
    {
        
    }

    public function run()
    {
        $uniqData = $this->context->uniqueStorage->checkRecordsUnique($this->container->data);
        
        foreach ($uniqData as $id => $isUniq) {
            if ($isUniq) {
                $this->container->data[$id][StatRecord::COUNT_UNQ] = 1; 
            }
        }
    }

    public function shouldProcess()
    {
        return $this->container->getDataCount() > 0 && $this->context->uniqueStorage;
    }
}