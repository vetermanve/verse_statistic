<?php


namespace Verse\Statistic\Core\Schema;


use Verse\Modular\ModularProcessor;
use Verse\Modular\ModularSchemaInterface;
use Verse\Statistic\Core\StatsModuleProto;
use Verse\Statistic\Core\Strategy\Aggregate\AcknowledgeEvents;
use Verse\Statistic\Core\Strategy\Aggregate\LoadEventsFromStream;
use Verse\Statistic\Core\Strategy\Compress\CompressDataStatRecordByUniqId;
use Verse\Statistic\Core\Strategy\Compress\CompressDataStatRecordDropUniqAndMakeResults;
use Verse\Statistic\Core\Strategy\Filter\FilterRawTimeRecords;
use Verse\Statistic\Core\Strategy\Grouping\GroupEventsToStatRecords;
use Verse\Statistic\Core\Strategy\Load\LoadKnownEventNamesAndHashes;
use Verse\Statistic\Core\Strategy\Load\LoadStatisticModels;
use Verse\Statistic\Core\Strategy\Split\AddScopedStatRecordsToGlobalScope;
use Verse\Statistic\Core\Strategy\Split\AddMonthAggregationRecords;
use Verse\Statistic\Core\Strategy\Split\AddDayAggregationRecords;
use Verse\Statistic\Core\Strategy\Split\AddHoursAggregationRecords;
use Verse\Statistic\Core\Strategy\Storing\StoreResults;
use Verse\Statistic\Core\Strategy\UniequeProcessing\LoadUniqDataForStatRecords;
use Verse\Statistic\Core\Strategy\UniequeProcessing\StoreUniqueToStorage;

class BasicStatsSchema extends StatsModuleProto implements ModularSchemaInterface
{

    /**
     * @param ModularProcessor $processor
     */
    public function configure($processor)
    {
        // load events configuration
        $processor->addStrategy(new LoadStatisticModels(), $processor::SECTION_VERY_FIRST);
        
        // load new events
        $processor->addStrategy(new LoadEventsFromStream(), $processor::SECTION_VERY_FIRST);
        
        // prepare some events info for grouping 
        $processor->addStrategy(new LoadKnownEventNamesAndHashes(), $processor::SECTION_BEFORE);
        
        // make events stats records by grouping 
        $processor->addStrategy(new GroupEventsToStatRecords(), $processor::SECTION_RUN);

        // add all scoped events to main scope too
        $processor->addStrategy(new AddScopedStatRecordsToGlobalScope(), $processor::SECTION_RUN);
        
        // split events by time period 
        $processor->addStrategy(new AddHoursAggregationRecords(), $processor::SECTION_RUN);
        $processor->addStrategy(new AddDayAggregationRecords(), $processor::SECTION_RUN);
        $processor->addStrategy(new AddMonthAggregationRecords(), $processor::SECTION_RUN);
        
        // remove raw time records
        $processor->addStrategy(new FilterRawTimeRecords(), $processor::SECTION_RUN);
        
        // compress all non-uniq records increasing count 
        $processor->addStrategy(new CompressDataStatRecordByUniqId(), $processor::SECTION_RUN);

        // load unique or not stat records in time scale 
        $processor->addStrategy(new LoadUniqDataForStatRecords(), $processor::SECTION_RUN);

        // compress records with dropping (incrementing) unique;
        $processor->addStrategy(new CompressDataStatRecordDropUniqAndMakeResults(), $processor::SECTION_RUN);
        
        // store results to db
        $processor->addStrategy(new StoreResults(), $processor::SECTION_AFTER);
        
        // store unique marks
        $processor->addStrategy(new StoreUniqueToStorage(), $processor::SECTION_AFTER);
        
        // acknowledge events
        $processor->addStrategy(new AcknowledgeEvents(),$processor::SECTION_VERY_LAST);
    }
}