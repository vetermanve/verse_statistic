<?php


namespace Verse\Statistic\Core\Strategy\Compress;


use Verse\Modular\ModularStrategyInterface;
use Verse\Statistic\Core\Model\StatRecord;
use Verse\Statistic\Core\StatsModuleProto;

class CompressDataStatRecordDropUniqAndMakeResults extends StatsModuleProto implements ModularStrategyInterface
{

    public function prepare()
    {
        
    }

    public function run()
    {
        foreach ($this->container->data as $statRecord) {
            $id = StatRecord::getRecordId($statRecord);

            // remember and optimise
            if (!isset($this->container->results[$id])) {
                unset($statRecord[StatRecord::UNIQUE_ID]);
                $this->container->results[$id] = $statRecord;
            } else {
                $this->container->results[$id][StatRecord::COUNT] += $statRecord[StatRecord::COUNT];
                $this->container->results[$id][StatRecord::COUNT_UNQ] += $statRecord[StatRecord::COUNT_UNQ];
            }
        }
    }

    public function shouldProcess()
    {
        return $this->container->data;
    }
}