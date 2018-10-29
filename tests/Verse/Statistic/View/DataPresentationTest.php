<?php


namespace Verse\Statistic\View;


use PHPUnit\Framework\TestCase;
use Verse\Statistic\Configuration\Grouping\BasicGroping;
use Verse\Statistic\Core\Model\StatRecord;
use Verse\Statistic\Core\Model\TimeScale;
use Verse\Statistic\Storage\Records\VerseStorageStatRecords;
use Verse\Statistic\View\Stats\ExampleViewStats;

class DataPresentationTest extends TestCase
{

    public function testDefaultPresentation()
    {
        $stats = new ExampleViewStats();
        $timeScale = TimeScale::HOUR;

        $dater = new Dater();
        $dater->setTimeScale(TimeScale::HOUR);
        $dater->setFromTime(strtotime('10.10.2018'));
        $dater->setToTime(strtotime('11.10.2018'));

        $dater->setStatisticConfiguration($stats);
        $dater->setFields($stats->getFields());
        $dater->setGrouping(new BasicGroping());

        $rows = $dater->getRows();
        $fields = $dater->getFields();

        $data = [];
        $check = [];
        foreach ($rows as $time) {
            $checkValues = [];
            
            foreach ($fields as $field => $fieldId) {
                $count = ceil(crc32($fieldId + $time) / 100000);
                $countUnq = ceil($count/100);
                
                $data[] = [
                    StatRecord::SCOPE_ID   => 0,
                    StatRecord::GROUP_TYPE => 1,
                    StatRecord::GROUP_ID   => 0,
                    StatRecord::EVENT_ID   => $fieldId,
                    StatRecord::COUNT      => $count,
                    StatRecord::COUNT_UNQ  => $countUnq,
                    StatRecord::TIME_SCALE => $timeScale,
                    StatRecord::TIME       => $time,
                ];

                $checkValues[] = [$count, $countUnq];
            }
            
            $check[$time] = $checkValues; 
        }
    
        $this->assertNotEmpty($data);
        $dater->setRawData($data);
        
        $dater->setCurrentView('default');
        $dater->buildViewData();
        
        $table = $dater->getTableData();
        $this->assertNotEmpty($table);
        $this->assertEquals($check, $table);
    }

    public function testCustomPresentation()
    {
        $stats = new ExampleViewStats();
        $timeScale = TimeScale::HOUR;

        $dater = new Dater();
        $dater->setTimeScale(TimeScale::HOUR);
        $dater->setFromTime(strtotime('10.10.2018'));
        $dater->setToTime(strtotime('11.10.2018'));

        $dater->setStatisticConfiguration($stats);
        $dater->setFields($stats->getFields());
        $dater->setGrouping(new BasicGroping());

        $rows = $dater->getRows();
        $fields = $dater->getFields();

        $data = [];
        $check = [];
        foreach ($rows as $time) {
            $checkValues = [];

            foreach ($fields as $field => $fieldId) {
                $count = ceil(crc32($fieldId + $time) / 100000);
                $countUnq = ceil($count/100);

                $data[] = [
                    StatRecord::SCOPE_ID   => 0,
                    StatRecord::GROUP_TYPE => 1,
                    StatRecord::GROUP_ID   => 0,
                    StatRecord::EVENT_ID   => $fieldId,
                    StatRecord::COUNT      => $count,
                    StatRecord::COUNT_UNQ  => $countUnq,
                    StatRecord::TIME_SCALE => $timeScale,
                    StatRecord::TIME       => $time,
                ];

                $checkValues[$field] = [$count, $countUnq];
            }

            $check[$time] = [
                [
                    round($checkValues[ExampleViewStats::F_CLICK][0]/$checkValues[ExampleViewStats::F_SHOW][0],3) 
                ],
                [
                    round($checkValues[ExampleViewStats::F_CLICK][1]/$checkValues[ExampleViewStats::F_SHOW][1],3)
                ]
            ];
        }

        $this->assertNotEmpty($data);
        $dater->setRawData($data);

        $dater->setCurrentView('custom');
        $dater->buildViewData();

        $table = $dater->getTableData();
        $this->assertNotEmpty($table);
        $this->assertEquals($check, $table);
    }
}