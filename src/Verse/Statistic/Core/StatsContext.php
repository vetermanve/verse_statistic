<?php


namespace Verse\Statistic\Core;


use Verse\Modular\ModularContextProto;
use Verse\Statistic\Configuration\GroupingFactory;
use Verse\Statistic\Configuration\StatisticFactory;

class StatsContext extends ModularContextProto
{
    const FILE_STATS_DIRECTORY = 'file-stats-dir';
    
    const READER_CHUNK_SIZE = 'reader-chunk-size';

    /**
     * @var GroupingFactory
     */
    public $groupingFactory;

    /**
     * @var StatisticFactory
     */
    public $statisticFactory;
}