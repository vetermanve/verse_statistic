<?php


namespace Verse\Statistic\Core\Strategy\Split;


use Verse\Modular\ModularStrategyInterface;
use Verse\Statistic\Core\Event;
use Verse\Statistic\Core\Model\StatRecord;
use Verse\Statistic\Core\StatsModuleProto;

class AddScopedStatRecordsToGlobalScope extends StatsModuleProto implements ModularStrategyInterface
{
    public function prepare()
    {
        
    }
    
    public function run()
    {
        $globalScope = [
            StatRecord::SCOPE_ID => 0
        ];

        $globalScopeStatRecords = [];
        foreach ($this->container->data as &$statRecord) {
            if ($statRecord[StatRecord::SCOPE_ID] > 0) {
                $globalScopeStatRecords[] = $globalScope + $statRecord;
            }
        } unset($statRecord);


        $this->container->addData($globalScopeStatRecords);
    }

    public function shouldProcess()
    {
        return $this->container->getDataCount() > 0;
    }
}