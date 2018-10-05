<?php


namespace Verse\Statistic\Core\Strategy\Filter;


use Verse\Modular\ModularStrategyInterface;
use Verse\Statistic\Core\Model\StatRecord;
use Verse\Statistic\Core\Model\TimeScale;
use Verse\Statistic\Core\StatsModuleProto;

class FilterRawTimeRecords extends StatsModuleProto implements ModularStrategyInterface
{

    public function prepare()
    {
        
    }

    public function run()
    {
        foreach ($this->container->data as $id => $rec) {
            if ($rec[StatRecord::TIME_SCALE] === TimeScale::RAW) {
                unset($this->container->data[$id]); 
            }
        }
    }

    public function shouldProcess()
    {
        return $this->container->data;
    }
}