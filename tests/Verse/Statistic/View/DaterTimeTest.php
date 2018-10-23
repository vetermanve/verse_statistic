<?php


namespace Verse\Statistic\View;


use PHPUnit\Framework\TestCase;
use Verse\Statistic\Core\Model\TimeScale;

class DaterTimeTest extends TestCase
{
    public function testHoursRows () 
    {
        $dater = new Dater();
        $dater->setTimeScale(TimeScale::HOUR);
        $dater->setFromTime(strtotime('11:00 01.01.2018'));
        $dater->setToTime(strtotime('23:00 01.01.2018'));


        $rows = $dater->getRows();
        $this->assertCount(13, $rows);

        $named = $dater->getRowsNamed();
        $expected = array (
            1514836800 => '01.01 23:00',
            1514833200 => '01.01 22:00',
            1514829600 => '01.01 21:00',
            1514826000 => '01.01 20:00',
            1514822400 => '01.01 19:00',
            1514818800 => '01.01 18:00',
            1514815200 => '01.01 17:00',
            1514811600 => '01.01 16:00',
            1514808000 => '01.01 15:00',
            1514804400 => '01.01 14:00',
            1514800800 => '01.01 13:00',
            1514797200 => '01.01 12:00',
            1514793600 => '01.01 11:00',
            1514790000 => '01.01 10:00',
        );
        
        $this->assertEquals($expected, $named);
    }
    
    public function testDaysRows () 
    {
        $dater = new Dater();
        $dater->setTimeScale(TimeScale::DAY);
        $dater->setFromTime(strtotime('-10 day'));
        $dater->setToTime(time());

        $rows = $dater->getRows();

        $this->assertCount(11, $rows);
    }

    public function testMonthRows ()
    {
        $dater = new Dater();
        $dater->setTimeScale(TimeScale::MONTH);
        $dater->setFromTime(strtotime('-5 month'));
        $dater->setToTime(time());

        $rows = $dater->getRows();

        $this->assertCount(5, $rows);
    }
}