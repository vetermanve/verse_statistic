<?php


namespace Verse\Statistic\Core\Strategy\Split;


use Verse\Modular\ModularStrategyInterface;
use Verse\Statistic\Core\Model\StatRecord;
use Verse\Statistic\Core\Model\TimeScale;
use Verse\Statistic\Core\StatsModuleProto;

class AddWeekAggregationRecords extends StatsModuleProto implements ModularStrategyInterface
{
    public function prepare()
    {
        
    }

    public function run()
    {
        $times = [];
        // split events 
        foreach ($this->container->data as $statRecord) {
            if ($statRecord[StatRecord::TIME_SCALE] !== TimeScale::RAW) {
                continue;
            }

            $rawTime = $statRecord[StatRecord::TIME];
            $time = $times[$rawTime] ?? $times[$rawTime] = $this->_makeAggregateTime($rawTime);

            $this->container->data[] = [
                    StatRecord::TIME       => $time,
                    StatRecord::TIME_SCALE => TimeScale::WEEK,
                ] + $statRecord;
        }
    }

    private function _makeAggregateTime($time) {
        // get mktime for first day of $time's week
        // N - ISO-8601 number of the day of the week - 1 (for Monday) through 7 (for Sunday)
        $weekDayOffset = date('N', $time) - 1;
        $time = strtotime("-{$weekDayOffset} days", $time);
        return (int)mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));
    }

    public function shouldProcess()
    {
        return $this->container->getDataCount() > 0;
    }
}