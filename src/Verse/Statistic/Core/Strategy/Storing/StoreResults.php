<?php


namespace Verse\Statistic\Core\Strategy\Storing;


use Verse\Modular\ModularStrategyInterface;
use Verse\Statistic\Core\StatsModuleProto;
use Verse\Statistic\Storage\Records\VerseStorageStatRecords;

class StoreResults extends StatsModuleProto implements ModularStrategyInterface
{

    /**
     * @var VerseStorageStatRecords
     */
    protected $storage;
    
    public function prepare()
    {
        $this->storage = $this->context->statsStorage;
    }

    public function run()
    {
        $this->container->resultsWrotten = $this->storage->addRecords($this->container->results);
    }

    public function shouldProcess()
    {
        return $this->container->results && $this->context->statsStorage; 
    }
}