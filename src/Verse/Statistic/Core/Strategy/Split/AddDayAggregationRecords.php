<?php


namespace Verse\Statistic\Core\Strategy\Split;


use Verse\Modular\ModularStrategyInterface;
use Verse\Statistic\Core\Model\StatRecord;
use Verse\Statistic\Core\Model\TimeScale;
use Verse\Statistic\Core\StatsModuleProto;

class AddDayAggregationRecords extends StatsModuleProto implements ModularStrategyInterface
{

    public function prepare()
    {
        
    }

    public function run()
    {
        // prevent time zone
        $saveTimeZone = date_default_timezone_get();
        date_default_timezone_set('UTC');

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
                    StatRecord::TIME_SCALE => TimeScale::DAY,
                ] + $statRecord;
        }

        // restore time zone
        date_default_timezone_set($saveTimeZone);
    }

    private function _makeAggregateTime($time) {
        return (int)mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));
    }

    public function shouldProcess()
    {
        return $this->container->getDataCount() > 0;
    }
}