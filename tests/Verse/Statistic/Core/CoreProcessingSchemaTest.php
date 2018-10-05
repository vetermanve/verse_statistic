<?php


namespace Verse\Statistic\Core;

use PHPUnit\Framework\TestCase;
use Verse\Statistic\Configuration\Grouping\BasicGroping;
use Verse\Statistic\Configuration\GroupingFactory;
use Verse\Statistic\Configuration\StatisticFactory;
use Verse\Statistic\Core\ExampleStats\ExampleVisitStatistic;
use Verse\Statistic\Core\Model\StatRecord;
use Verse\Statistic\Core\Model\TimeScale;
use Verse\Statistic\Core\Schema\DefaultTimeAggregationSchema;
use Verse\Statistic\Core\Schema\FileBasedStatsSchema;
use Verse\Statistic\Core\Schema\LoadEventsFromFilesSchema;
use Verse\Statistic\Core\Schema\GroupEventsToStatRecordsSchema;
use Verse\Statistic\Core\Strategy\Grouping\GroupEventsToStatRecords;

class CoreProcessingSchemaTest extends TestCase
{
    public function testFileStatsSchema () 
    {
        $context = new StatsContext();
        
        $context->groupingFactory = new GroupingFactory();
        $context->groupingFactory->addGroupingModel(BasicGroping::TYPE, new BasicGroping());
        
        $context->statisticFactory = new StatisticFactory();
        $context->statisticFactory->addStats(new ExampleVisitStatistic());
        
        $context->set(StatsContext::FILE_STATS_DIRECTORY, __DIR__.DIRECTORY_SEPARATOR.'test-stats');
        
        $container = new StatsContainer();
        
        $processor = new StatProcessor();
        $processor->setContext($context);
        $processor->setContainer($container);
        
        $processor->addSchema(new LoadEventsFromFilesSchema());
        $processor->addSchema(new GroupEventsToStatRecordsSchema());
        $processor->addSchema(new DefaultTimeAggregationSchema());
        
        $processor->run();
        
        $this->assertNotEmpty($container->data);

        $rawRecords = array_filter($container->data, function ($rec) {
            return $rec[StatRecord::TIME_SCALE] === TimeScale::RAW;
        });

        $this->assertNotEmpty($rawRecords);
        $rawRecordsCount = count($rawRecords);
        
        $hourAggregated = array_filter($container->data, function ($rec) {
            return $rec[StatRecord::TIME_SCALE] === TimeScale::HOUR; 
        });
        $this->assertCount($rawRecordsCount, $hourAggregated);

        $dayAggregated = array_filter($container->data, function ($rec) {
            return $rec[StatRecord::TIME_SCALE] === TimeScale::DAY;
        });
        $this->assertCount($rawRecordsCount, $dayAggregated);

        $monthAggregated = array_filter($container->data, function ($rec) {
            return $rec[StatRecord::TIME_SCALE] === TimeScale::MONTH;
        });

        $this->assertCount($rawRecordsCount, $monthAggregated);
    }
}