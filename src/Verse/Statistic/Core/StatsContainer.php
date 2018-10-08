<?php


namespace Verse\Statistic\Core;


use Verse\Modular\ModularContainerProto;
use Verse\Statistic\Aggregate\EventsContainer;
use Verse\Statistic\Configuration\Stats\AbstractStatistic;

class StatsContainer extends ModularContainerProto
{
    /**
     * @var AbstractStatistic[]
     */
    public $statisticModels = [];
    
    public $eventNamesIdx = [];

    /**
     * @var EventsContainer
     */
    public $evensContainer;
    
    public $resultsWrotten = false;
}