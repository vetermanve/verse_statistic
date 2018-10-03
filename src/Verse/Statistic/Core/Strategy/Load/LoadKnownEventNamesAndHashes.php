<?php


namespace Verse\Statistic\Core\Strategy\Load;


use Verse\Modular\ModularStrategyInterface;
use Verse\Statistic\Core\StatsModuleProto;

class LoadKnownEventNamesAndHashes extends StatsModuleProto implements ModularStrategyInterface
{

    public function prepare()
    {
        
    }

    public function run()
    {
        $eventContainer = $this->container->evensContainer;
        
        foreach ($this->container->statisticModels as $stat) {
            foreach ($stat->getFields() as $eventName) {
                if (!isset($eventContainer->eventNamesHashes[$eventName])) {
                    $eventContainer->eventNamesHashes[$eventName] = crc32($eventName);
                }
            }
        }
    }

    public function shouldProcess()
    {
        return \count($this->container->statisticModels) > 0 && $this->container->evensContainer;
    }
}