<?php


namespace Verse\Statistic\Configuration;


class StatisticFactory
{
    /**
     * @var Stats\AbstractStatistic[]
     */
    protected $stats = [];
    
    /**
     * @param Stats\AbstractStatistic $stats
     */
    public function addStats(Stats\AbstractStatistic $stats)
    {
        $this->stats[$stats->getId()] = $stats;
    }

    /**
     * @param $statsId
     * @return null|Stats\AbstractStatistic
     */
    public function getStatsById ($statsId) 
    {
        return $this->stats[$statsId] ?? null;
    }
    
    /**
     * @return Stats\AbstractStatistic[]
     */
    public function getStats() : array 
    {
        return $this->stats;
    }
}