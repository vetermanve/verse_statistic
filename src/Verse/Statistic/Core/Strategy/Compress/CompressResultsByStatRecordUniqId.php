<?php


namespace Verse\Statistic\Core\Strategy\Compress;


use Verse\Modular\ModularStrategyInterface;
use Verse\Statistic\Core\Model\StatRecord;
use Verse\Statistic\Core\StatsModuleProto;

class CompressResultsByStatRecordUniqId extends StatsModuleProto implements ModularStrategyInterface
{

    public function prepare()
    {
        
    }

    public function run()
    {
        $compressedResults = [];
        
        foreach ($this->container->results as $statRecord) {
            $id = StatRecord::getRecordUniqId($statRecord);

            // remember and optimi
            if (!isset($compressedResults[$id])) {
                $compressedResults[$id] = $statRecord;
            } else {
                $compressedResults[$id][StatRecord::COUNT] += $statRecord[StatRecord::COUNT];
            }    
        }
        
        $this->container->results = $compressedResults;
    }

    public function shouldProcess()
    {
        return $this->container->results;
    }
}