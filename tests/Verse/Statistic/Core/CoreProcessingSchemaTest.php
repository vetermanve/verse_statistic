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
use Verse\Statistic\Core\Schema\BasicStatsSchema;
use Verse\Statistic\Core\Schema\LoadEventsFromFilesSchema;
use Verse\Statistic\Core\Schema\GroupEventsToStatRecordsSchema;
use Verse\Statistic\Storage\Records\VerseStorageStatRecords;
use Verse\Statistic\Storage\Unique\VerseStorageUnique;

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

        $weekAggregated = array_filter($container->data, function ($rec) {
            return $rec[StatRecord::TIME_SCALE] === TimeScale::WEEK;
        });

        $this->assertCount($rawRecordsCount, $weekAggregated);
    }

    public function testFileStatsFullSchema ()
    {
        $context = new StatsContext();
        
        $jsonFileStoragePath = __DIR__.DIRECTORY_SEPARATOR.'test-stats-full-schema'.DIRECTORY_SEPARATOR.'jbase';
        
        //// stats storage
        $storage = new VerseStorageStatRecords();
        $storage->getContext()->set(VerseStorageStatRecords::DATA_ROOT_PATH, $jsonFileStoragePath);
        
        // clearing storage
        $data = $storage->search()->find([], 100000, __METHOD__);
        foreach ($data as $id => $_) {
            $storage->write()->remove($id, __METHOD__);    
        }
        // bind storage
        $context->statsStorage = $storage;

        //// unique storage
        $uniqueStorage = new VerseStorageUnique();
        $uniqueStorage->getContext()->set(VerseStorageStatRecords::DATA_ROOT_PATH, $jsonFileStoragePath);

        // clearing storage
        $uniqueData = $uniqueStorage->search()->find([], 100000, __METHOD__);
        foreach ($uniqueData as $id => $_) {
            $uniqueStorage->write()->remove($id, __METHOD__);
        }
        
        // bind storage
        $context->uniqueStorage = $uniqueStorage;
        
        // grouping
        $context->groupingFactory = new GroupingFactory();
        $context->groupingFactory->addGroupingModel(BasicGroping::TYPE, new BasicGroping());

        // stats
        $context->statisticFactory = new StatisticFactory();
        $context->statisticFactory->addStats(new ExampleVisitStatistic());

        // event stream
        $context->eventsStream = new FilesDirectoryEventStream();
        $context->eventsStream->setStatFilesDirectory(__DIR__.DIRECTORY_SEPARATOR.'test-stats-full-schema');
        $context->eventsStream->forgetStreamPosition();
        
        // container
        $container = new StatsContainer();

        // processor
        $processor = new StatProcessor();
        $processor->setContext($context);
        $processor->setContainer($container);

        // add schema
        $processor->addSchema(new BasicStatsSchema());

        // run magic
        $processor->run();

        // check magic heppend
        $this->assertNotEmpty($container->results);
        
        // should read 9 events
        $this->assertCount(9, $container->evensContainer->events);
        
        // should be 18 data events
        $this->assertCount(18, $container->data);
        
        // should be 6 by unique results
        $this->assertCount(6, $container->results);
        
        // results should be written correctly 
        $this->assertTrue($container->resultsWrotten);
        
        $stored = $storage->search()->find([], 100, __METHOD__);
        
        array_walk($stored, function (&$rec) {
            unset($rec['id']);
        });
        
        $this->assertEquals($container->results, $stored);
        
        // check unique results stored
        $uniqData = $uniqueStorage->search()->find([], 100, __METHOD__);
        $this->assertNotEmpty($uniqData);
        
    }
}