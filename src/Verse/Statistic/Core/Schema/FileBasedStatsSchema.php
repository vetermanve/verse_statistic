<?php


namespace Verse\Statistic\Core\Schema;


use Verse\Modular\ModularProcessor;
use Verse\Modular\ModularSchemaInterface;
use Verse\Statistic\Core\StatsModuleProto;
use Verse\Statistic\Core\Strategy\Aggregate\LoadEventsFromFiles;
use Verse\Statistic\Core\Strategy\Compress\CompressDataStatRecordByUniqId;
use Verse\Statistic\Core\Strategy\Filter\FilterRawTimeRecords;
use Verse\Statistic\Core\Strategy\Grouping\GroupEventsToStatRecords;
use Verse\Statistic\Core\Strategy\Load\LoadKnownEventNamesAndHashes;
use Verse\Statistic\Core\Strategy\Load\LoadStatisticModels;
use Verse\Statistic\Core\Strategy\Split\AddScopedStatRecordsToGlobalScope;
use Verse\Statistic\Core\Strategy\Split\AddMonthAggregationRecords;
use Verse\Statistic\Core\Strategy\Split\AddDayAggregationRecords;
use Verse\Statistic\Core\Strategy\Split\AddHoursAggregationRecords;

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
        $processor->addStrategy(new GroupEventsToStatRecords(), $processor::SECTION_RUN);

        // add all scoped events to main scope too
        $processor->addStrategy(new AddScopedStatRecordsToGlobalScope(), $processor::SECTION_RUN);
        
        // split events by time period 
        $processor->addStrategy(new AddHoursAggregationRecords(), $processor::SECTION_RUN);
        $processor->addStrategy(new AddDayAggregationRecords(), $processor::SECTION_RUN);
        $processor->addStrategy(new AddMonthAggregationRecords(), $processor::SECTION_RUN);
        
        $processor->addStrategy(new FilterRawTimeRecords(), $processor::SECTION_RUN);
        
        $processor->addStrategy(new CompressDataStatRecordByUniqId(), $processor::SECTION_RUN);
    }
}