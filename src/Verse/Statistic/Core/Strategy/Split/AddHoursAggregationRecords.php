<?php


namespace Verse\Statistic\Core\Strategy\Split;


use Verse\Modular\ModularStrategyInterface;
use Verse\Statistic\Core\Event;
use Verse\Statistic\Core\Model\StatRecord;
use Verse\Statistic\Core\Model\TimeScale;
use Verse\Statistic\Core\StatsModuleProto;

class AddHoursAggregationRecords extends StatsModuleProto implements ModularStrategyInterface
{

    public function prepare()
    {
        
    }

    public function run()
    {
        foreach ($this->container->data as $statRecord) {
            if ($statRecord[StatRecord::TIME_SCALE] !== TimeScale::RAW) {
                continue;
            }
            
            $this->container->data[] = [
                    StatRecord::TIME       => (int)ceil($statRecord[StatRecord::TIME] / 3600) * 3600,
                    StatRecord::TIME_SCALE => TimeScale::HOUR,
                ] + $statRecord;
        }
    }

    public function shouldProcess()
    {
        return $this->container->getDataCount() > 0;
    }
}