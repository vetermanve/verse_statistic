<?php


namespace Verse\Statistic\Core;


use PHPUnit\Framework\TestCase;
use Verse\Router\Router;
use Verse\Router\RouterConfig;
use Verse\Router\RouterRegistry;
use Verse\Statistic\Aggregate\EventStream\AmqpRouterEventStream;
use Verse\Statistic\Configuration\Grouping\BasicGroping;
use Verse\Statistic\Configuration\GroupingFactory;
use Verse\Statistic\Configuration\StatisticFactory;
use Verse\Statistic\Core\ExampleStats\ExampleVisitStatistic;
use Verse\Statistic\Core\Schema\BasicStatsSchema;
use Verse\Statistic\Storage\Records\VerseStorageStatRecords;
use Verse\Statistic\Storage\Unique\VerseStorageUnique;
use Verse\Statistic\WriteClient\Encoder\JsonEncoder;
use Verse\Statistic\WriteClient\Stats;
use Verse\Statistic\WriteClient\Transport\AmqpRouterTransport;

class AmqpStatsWorkflowTest extends TestCase
{
    public function testAmqpClientAndReadStream () 
    {
        // queue and connection defenition;
        $queueName = __FUNCTION__;
        $router = new Router();
        $router->init();
        
        // event amqp stream
        $stream = new AmqpRouterEventStream();
        $stream->setConsumer($router->getConsumer($queueName, 0.1));
        
        // clear stream 
        while ($stream->get()) {
            true;
        }
        
        $clientTransport = new AmqpRouterTransport();
        $clientTransport->setQueueName($queueName);
        $clientTransport->setRouter($router);

        $client = new Stats();
        $client->setEncoder(new JsonEncoder());
        $client->setTransport($clientTransport);
        
        $eventDataSent = $client->makeEventData('test', microtime(1), 'test-company1');
        $client->sendEventData($eventDataSent);
        
        $eventDataReceived = $stream->get();
        $this->assertEquals($eventDataSent, $eventDataReceived->body);
    }
    
    
    public function testSchemaWithAmqp () 
    {
        $context = new StatsContext();

        $verseFileDatabaseRoot = __DIR__.DIRECTORY_SEPARATOR.'test-stats-amqp-schema'.DIRECTORY_SEPARATOR.'jbase';

        //// stats storage
        $storage = new VerseStorageStatRecords();
        $storage->getContext()->set(VerseStorageStatRecords::DATA_ROOT_PATH, $verseFileDatabaseRoot);

        // clearing storage
        $data = $storage->search()->find([], 100000, __METHOD__);
        foreach ($data as $id => $_) {
            $storage->write()->remove($id, __METHOD__);
        }
        // bind storage
        $context->statsStorage = $storage;

        //// unique storage
        $uniqueStorage = new VerseStorageUnique();
        $uniqueStorage->getContext()->set(VerseStorageStatRecords::DATA_ROOT_PATH, $verseFileDatabaseRoot);

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


        $queueName = __FUNCTION__;
        $router = new Router();
        $router->init();

        // event amqp stream
        $stream = new AmqpRouterEventStream();
        $stream->setConsumer($router->getConsumer($queueName, 0.1));

        // clear stream 
        while ($stream->get()) {
            true;
        }
        
        // bind stream
        $context->eventsStream = $stream;

        // create client transport
        $clientTransport = new AmqpRouterTransport();
        $clientTransport->setQueueName($queueName);
        $clientTransport->setRouter($router);
        
        // create client
        $client = new Stats();
        $client->setEncoder(new JsonEncoder());
        $client->setTransport($clientTransport);

        // push test events
        $testEvents = [];
        
        $eventsTime = time();
        
        // first time
        foreach (\range(1, 10) as $id) {
            $eventDataSent = $client->makeEventData('test', $id, 0, [], 1, $eventsTime);
            $client->sendEventData($eventDataSent);
            $testEvents[] = $eventDataSent; 
        }

        // one more time
        foreach (\range(1, 10) as $id) {
            $eventDataSent = $client->makeEventData('test', $id, 0, [], 1, $eventsTime);
            $client->sendEventData($eventDataSent);
            $testEvents[] = $eventDataSent;
        }
        
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

        // check magic happened
        $this->assertNotEmpty($container->results);

        // should read 20 events
        $this->assertCount(20, $container->evensContainer->events);
        $this->assertEquals($testEvents, $container->evensContainer->events);

        // should be 30 time-spliet unique-aggregated results
        $this->assertCount(30, $container->data);

        // should be 3 by time-split summed results, 
        $this->assertCount(3, $container->results);

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