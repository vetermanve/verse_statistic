<?php


namespace Verse\Statistic\Core;


use PHPUnit\Framework\TestCase;
use Verse\Statistic\Core\Model\StatRecord;
use Verse\Statistic\Core\Model\TimeScale;
use Verse\Statistic\Core\Strategy\Compress\CompressDataStatRecordByUniqId;

class CompressingStrategiesTest extends TestCase
{
    private function makeRecords($eventIds, $uniques, $times)
    {
        $records = [];
        foreach ($eventIds as $eventId) {
            foreach ($uniques as $unique) {
                foreach ($times as $time) {
                    $records[] = [
                        StatRecord::EVENT_ID   => $eventId,
                        StatRecord::SCOPE_ID   => 0,
                        StatRecord::GROUP_TYPE => 9,
                        StatRecord::GROUP_ID   => 0,
                        StatRecord::UNIQUE_ID  => $unique,
                        StatRecord::TIME       => $time,
                        StatRecord::TIME_SCALE => TimeScale::RAW,
                        StatRecord::COUNT      => 1,
                        StatRecord::COUNT_UNQ  => 0,
                    ];
                }
            }
        }
        
        return $records;
    }


    public function testCompressingByUinqId()
    {
        $processor = new StatProcessor();
        $processor->setContext(new StatsContext());

        $container = new StatsContainer();
        
        // make test data
        $eventsIds = [1,2,3];
        $uniqueIds = [1,1];
        $times = [1,1,2,2];
        
        $container->data = $this->makeRecords($eventsIds, $uniqueIds, $times);
        
        $allEventCountShouldBe = \count($eventsIds) * \count($uniqueIds) * \count($times);
        
        // check test data correct generated
        $this->assertCount($allEventCountShouldBe, $container->data);
        
        // bind test data to processor
        $processor->setContainer($container);
        $processor->addStrategy(new CompressDataStatRecordByUniqId());
        $processor->run();
        
        $allFilteredEventsCountShouldBe = \count(\array_unique($eventsIds)) * \count(\array_unique($uniqueIds)) * \count(\array_unique($times));
        
        // check data filtered by count
        $this->assertCount($allFilteredEventsCountShouldBe, $container->data);
        
        $compressedValueShouldBe = $allEventCountShouldBe/$allFilteredEventsCountShouldBe;
        foreach ($container->data as $result) {
            $this->assertEquals($compressedValueShouldBe, $result[StatRecord::COUNT]);
        }
    }
}