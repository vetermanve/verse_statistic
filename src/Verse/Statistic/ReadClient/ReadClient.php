<?php


namespace Verse\Statistic\ReadClient;


use Verse\Statistic\Configuration\StatisticFactory;

class ReadClient
{
    public function getAllStatistics (StatisticFactory $statisticFactory) 
    {
        $results = [];
        
        foreach ($statisticFactory->getStats() as $statistic) {
            $results[$statistic->getId()] = $statistic->getName();
        }
        
        return $results;
    }
}