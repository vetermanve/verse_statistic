<?php


namespace Verse\Statistic\Core\Schema;


use Verse\Modular\ModularProcessor;
use Verse\Modular\ModularSchemaInterface;
use Verse\Statistic\Core\StatsModuleProto;
use Verse\Statistic\Core\Strategy\Aggregate\LoadEventsFromFiles;
use Verse\Statistic\Core\Strategy\Grouping\SplitEventsToStatRecords;
use Verse\Statistic\Core\Strategy\Load\LoadKnownEventNamesAndHashes;
use Verse\Statistic\Core\Strategy\Load\LoadStatisticModels;
use Verse\Statistic\Core\Strategy\Split\AddScopedStatRecordsToGlobalScope;
use Verse\Statistic\Core\Strategy\Split\MakeResultsMonthAggregation;
use Verse\Statistic\Core\Strategy\Split\MakeResultsDayAggregation;
use Verse\Statistic\Core\Strategy\Split\MakeResultsHoursAggregation;

class FileBasedStatsSchema extends StatsModuleProto implements ModularSchemaInterface
{

    /**
     * @param ModularProcessor $processor
     */
    public function configure($processor)
    {
        // load events configuration
        $processor->addStrategy(new LoadStatisticModels(), $processor::SECTION_VERY_FIRST);
        
        // load new events
        $processor->addStrategy(new LoadEventsFromFiles(), $processor::SECTION_RUN);
        $processor->addStrategy(new LoadKnownEventNamesAndHashes(), $processor::SECTION_RUN);
        
        // make events stats records by grouping 
        $processor->addStrategy(new SplitEventsToStatRecords(), $processor::SECTION_RUN);

        // add all scoped events to main scope too
        $processor->addStrategy(new AddScopedStatRecordsToGlobalScope(), $processor::SECTION_RUN);
        
        // split events by time period 
        $processor->addStrategy(new MakeResultsHoursAggregation(), $processor::SECTION_RUN);
        $processor->addStrategy(new MakeResultsDayAggregation(), $processor::SECTION_RUN);
        $processor->addStrategy(new MakeResultsMonthAggregation(), $processor::SECTION_RUN);
    }
}