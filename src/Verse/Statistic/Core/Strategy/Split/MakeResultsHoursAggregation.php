<?php


namespace Verse\Statistic\Core\Strategy\Split;


use Verse\Modular\ModularStrategyInterface;
use Verse\Statistic\Core\Event;
use Verse\Statistic\Core\Model\StatRecord;
use Verse\Statistic\Core\Model\TimeScale;
use Verse\Statistic\Core\StatsModuleProto;

class MakeResultsHoursAggregation extends StatsModuleProto implements ModularStrategyInterface
{

    public function prepare()
    {
        
    }

    public function run()
    {
        foreach ($this->container->data as $statRecord) {
            $this->container->results[] = [
                StatRecord::TIME_ID   => (int)ceil($statRecord[StatRecord::TIME_RAW] / 3600) * 3600,
                StatRecord::TIME_TYPE => TimeScale::HOUR,
                StatRecord::TIME_RAW => 0,
            ] + $statRecord;
        }
    }

    public function shouldProcess()
    {
        return $this->container->getDataCount() > 0;
    }
}