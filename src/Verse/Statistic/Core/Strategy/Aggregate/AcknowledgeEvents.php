<?php


namespace Verse\Statistic\Core\Strategy\Aggregate;


use Verse\Modular\ModularStrategyInterface;
use Verse\Statistic\Core\StatsModuleProto;

class AcknowledgeEvents extends StatsModuleProto implements ModularStrategyInterface
{

    public function prepare()
    {
        
    }

    public function run()
    {
        $this->context->eventsStream->acknowledgePosition();
    }

    public function shouldProcess()
    {
        return $this->context->eventsStream && $this->container->resultsWrotten;
    }
}