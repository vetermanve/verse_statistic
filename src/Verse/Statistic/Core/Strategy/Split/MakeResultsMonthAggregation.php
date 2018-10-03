<?php


namespace Verse\Statistic\Core\Strategy\Split;


use Verse\Modular\ModularStrategyInterface;
use Verse\Statistic\Core\Event;
use Verse\Statistic\Core\Model\StatRecord;
use Verse\Statistic\Core\Model\TimeScale;
use Verse\Statistic\Core\StatsModuleProto;

class MakeResultsMonthAggregation extends StatsModuleProto implements ModularStrategyInterface
{

    public function prepare()
    {
        
    }

    public function run()
    {
        // prevent time zone
        $saveTimeZone = date_default_timezone_get();
        date_default_timezone_set('UTC');

        $times = array_column($this->container->data, StatRecord::TIME_RAW, StatRecord::TIME_RAW);

        $timeMarks = [];
        foreach ($times as $time) {
            $m = date('m', $time);
            $y = date('Y', $time);

            $timeMarks[$time] = (int)mktime(0, 0, 2, $m, 1, $y);
        }

        // restore time zone
        date_default_timezone_set($saveTimeZone);


        // split events 
        foreach ($this->container->data as $statRecord) {
            $this->container->results[] = [
                    StatRecord::TIME_ID   => $timeMarks[$statRecord[StatRecord::TIME_RAW]],
                    StatRecord::TIME_TYPE => TimeScale::DAY,
                    StatRecord::TIME_RAW => 0,
                ] + $statRecord;
        }
    }

    public function shouldProcess()
    {
        return $this->container->getDataCount() > 0;
    }
}