<?php

namespace Verse\Statistic;

use PHPUnit\Framework\TestCase;
use Verse\Statistic\WriteClient\Encoder\JsonEncoder;
use Verse\Statistic\WriteClient\Stats;
use Verse\Statistic\WriteClient\Transport\InMemoryTransport;
use Verse\Statistic\WriteClient\Transport\LocalFileTransport;

class WriteClientTest extends TestCase
{
    
    public function testInMemoryTransport () 
    {
        $this->assertTrue(true);
        
        $client = new Stats();
        
        $encoder = new JsonEncoder();
        $client->setEncoder($encoder);
        
        $transport = new InMemoryTransport();
        $client->setTransport($transport);
        
        $eventName = 'someEvent';
        $userId = 123;
        $count = 1;
        $scope = 22;
        $time = time();
        
        $context = [
            'hasContext' => true,  
        ];
        
        $client->event($eventName, $userId, $scope, $context, $count, $time);
        
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

    public function testAutoEventTime ()
    {
        $this->assertTrue(true);

        $client = new Stats();

        $encoder = new JsonEncoder();
        $client->setEncoder($encoder);

        $transport = new InMemoryTransport();
        $client->setTransport($transport);

        $eventName = 'someEvent';
        $userId = 123;
        $scope = 0;

        $startTime = \time();
        $client->event($eventName, $userId, $scope);
        $endTime = \time();

        $events = $transport->getEvents();
        $this->assertCount(1, $events);

        $eventEncoded = $events[0];
        $this->assertNotEmpty($eventEncoded);

        $eventDecoded = $encoder->decode($eventEncoded);
        
        $this->assertNotEmpty($eventDecoded[Stats::ST_TIME]);
        $eventTime = $eventDecoded[Stats::ST_TIME];
        
        $this->assertGreaterThanOrEqual($startTime, $eventTime);
        $this->assertLessThanOrEqual($endTime, $eventTime);
    }

    public function testFileTransport ()
    {
        $this->assertTrue(true);

        $client = new Stats();

        $encoder = new JsonEncoder();
        $client->setEncoder($encoder);

        $dir = __DIR__.'/data/';
        $transport = new LocalFileTransport();
        $transport->setStatFilesDirectory($dir);
        $file = $transport->getCurrentFileName();
        if (file_exists($file)) {
            unlink($file);    
        }
        
        $client->setTransport($transport);

        $eventName = 'someEvent';
        $userId = 123;
        $scope = 0;

        $time = \time();
        $client->event($eventName, $userId, $scope, [], 1, $time);
        $client->event($eventName, $userId + 1, $scope, [], 1, $time);

        $file = $transport->getCurrentFileName();
        $rawEvents = trim(file_get_contents($file), "\n");
        $this->assertNotEmpty($rawEvents);
        
        $eventsRaw = explode("\n", $rawEvents);
        $events = [];
        foreach ($eventsRaw as $eventRaw) {
            $events[] = $encoder->decode($eventRaw);
        }
        
        $this->assertCount(2, $events);

        $expectedEventsData = [
            [
                Stats::ST_EVENT_NAME => $eventName,
                Stats::ST_USER_ID    => $userId,
                Stats::ST_COUNT      => 1,
                Stats::ST_CONTEXT    => [],
                Stats::ST_TIME       => $time,
                Stats::ST_SCOPE_ID   => $scope,
            ],
            [
                Stats::ST_EVENT_NAME => $eventName,
                Stats::ST_USER_ID    => $userId + 1,
                Stats::ST_COUNT      => 1,
                Stats::ST_CONTEXT    => [],
                Stats::ST_TIME       => $time,
                Stats::ST_SCOPE_ID   => $scope,
            ],
        ];
        
        $eventEncoded = $eventsRaw[0];
        $this->assertNotEmpty($eventEncoded);

        $this->assertEquals($expectedEventsData, $events);
    }
}