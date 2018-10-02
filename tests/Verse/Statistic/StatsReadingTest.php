<?php


namespace Verse\Statistic;


use PHPUnit\Framework\TestCase;

use Verse\Statistic\WriteClient\Encoder\JsonEncoder;
use Verse\Statistic\WriteClient\Stats;
use Verse\Statistic\WriteClient\Transport\InMemoryTransport;

class StatsReadingTest extends TestCase
{

    /**
     * @var Stats
     */
    private $statsClient;
    
    protected function setUp()
    {
        parent::setUp();
        
        $client = new Stats();

        $encoder = new JsonEncoder();
        $client->setEncoder($encoder);

        $transport = new InMemoryTransport();
        $client->setTransport($transport);        
        
        $this->statsClient = $client; 
    }

    public function testReadingStats () 
    {
        $client = $this->statsClient;

        $eventName = __FUNCTION__;
        $scope = 22;
        
        $events = [];
        $userIds = range(5, 25);
        
        foreach ($userIds as $userId) {
            $context = [
                'forUser' => $userId,
            ];
            $event = $client->makeEventData($eventName, $userId, $scope, $context, \rand(1, 100));
            $client->sendEventData($event);
            
            $events[] = $event;
        }
        
        $this->assertNotEmpty($events);

        $stream = new AmqpRouterEventStream();
        $stream->setConsumer($consumer);

        $container = new EventsContainer();

        $reader = new EventStreamReader();
        $reader->setChunkSize(100);
        $reader->setEventsContainer($container);

        $reader->prepareCycle();
        $reader->readChunk();
        

        $events = $transport->getEvents();
        $this->assertCount(1, $events);

        $eventEncoded = $events[0];
        $this->assertNotEmpty($eventEncoded);

        $expectedEventData = [
            Stats::ST_EVENT_NAME => $eventName,
            Stats::ST_USER_ID    => $userId,
            Stats::ST_COUNT      => $count,
            Stats::ST_CONTEXT    => $context,
            Stats::ST_TIME       => $time,
            Stats::ST_SCOPE_ID   => $scope
        ];

        $eventDecoded = $encoder->decode($eventEncoded);
        $this->assertEquals($expectedEventData, $eventDecoded);
        
        
        
    }
    
    
    
}