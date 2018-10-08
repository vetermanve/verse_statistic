<?php


namespace Verse\Statistic\Core;

use PHPUnit\Framework\TestCase;
use Verse\Statistic\Aggregate\EventStream\FilesDirectoryEventStream;
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
use Verse\Statistic\Storage\Records\VerseStorageStatRecords;

class CoreProcessingSchemaTest extends TestCase
{
    public function testFileStatsTimeAggregationSchema () 
    {
        $context = new StatsContext();
        
        $context->groupingFactory = new GroupingFactory();
        $context->groupingFactory->addGroupingModel(BasicGroping::TYPE, new BasicGroping());
        
        $context->statisticFactory = new StatisticFactory();
        $context->statisticFactory->addStats(new ExampleVisitStatistic());
        
        
        $context->eventsStream = new FilesDirectoryEventStream();
        $context->eventsStream->setStatFilesDirectory(__DIR__.DIRECTORY_SEPARATOR.'test-stats');
        
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

    public function testFileStatsFullSchema ()
    {
        $context = new StatsContext();
        
        $storage = new VerseStorageStatRecords();
        $storage->setDataRootPath(__DIR__.DIRECTORY_SEPARATOR.'test-stats-full-schema'.DIRECTORY_SEPARATOR.'storage');
        $data = $storage->search()->find([], 100000, __METHOD__);
        foreach ($data as $id => $_) {
            $storage->write()->remove($id, __METHOD__);    
        }
        
        $context->groupingFactory = new GroupingFactory();
        $context->groupingFactory->addGroupingModel(BasicGroping::TYPE, new BasicGroping());

        $context->statisticFactory = new StatisticFactory();
        $context->statisticFactory->addStats(new ExampleVisitStatistic());

        $context->eventsStream = new FilesDirectoryEventStream();
        $context->eventsStream->setStatFilesDirectory(__DIR__.DIRECTORY_SEPARATOR.'test-stats-full-schema');
        $context->eventsStream->forgetStreamPosition();
        
        $context->statsStorage = $storage;

        $container = new StatsContainer();

        $processor = new StatProcessor();
        $processor->setContext($context);
        $processor->setContainer($container);

        $processor->addSchema(new FileBasedStatsSchema());

        $processor->run();

        $this->assertNotEmpty($container->results);
        $this->assertTrue($container->resultsWrotten);
    }
}