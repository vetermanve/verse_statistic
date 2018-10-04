<?php


namespace Verse\Statistic\Core\Schema;


use Verse\Modular\ModularSchemaInterface;
use Verse\Statistic\Core\StatProcessor;
use Verse\Statistic\Core\StatsModuleProto;
use Verse\Statistic\Core\Strategy\Split\AddDayAggregationRecords;
use Verse\Statistic\Core\Strategy\Split\AddHoursAggregationRecords;
use Verse\Statistic\Core\Strategy\Split\AddMonthAggregationRecords;

class DefaultTimeAggregationSchema extends StatsModuleProto implements ModularSchemaInterface
{

    /**
     * @param StatProcessor $processor
     */
    public function configure($processor)
    {
        // split events by time period 
        $processor->addStrategy(new AddHoursAggregationRecords(), $processor::SECTION_RUN);
        $processor->addStrategy(new AddDayAggregationRecords(), $processor::SECTION_RUN);
        $processor->addStrategy(new AddMonthAggregationRecords(), $processor::SECTION_RUN);
    }
}