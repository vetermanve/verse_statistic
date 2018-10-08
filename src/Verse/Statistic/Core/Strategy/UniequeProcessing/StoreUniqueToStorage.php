<?php


namespace Verse\Statistic\Core\Strategy\UniequeProcessing;


use Verse\Modular\ModularStrategyInterface;
use Verse\Statistic\Core\StatsModuleProto;

class StoreUniqueToStorage extends StatsModuleProto implements ModularStrategyInterface
{

    public function prepare()
    {
        
    }

    public function run()
    {
        $this->context->uniqueStorage->storeRecordsUnique($this->container->data);
    }

    public function shouldProcess()
    {
        return $this->container->resultsWrotten && $this->context->uniqueStorage;
    }
}