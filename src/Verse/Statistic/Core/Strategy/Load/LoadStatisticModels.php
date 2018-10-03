<?php


namespace Verse\Statistic\Core\Strategy\Load;


use Verse\Modular\ModularStrategyInterface;
use Verse\Statistic\Core\StatsModuleProto;

class LoadStatisticModels extends StatsModuleProto implements ModularStrategyInterface
{

    public function prepare()
    {
        
    }

    public function run()
    {
        $this->container->statisticModels = $this->context->statisticFactory->getStats();        
    }

    public function shouldProcess()
    {
        return true;
    }
}