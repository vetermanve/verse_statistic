<?php


namespace Verse\Statistic\View\ColumnFunction;

class TimeShiftDay extends AbstractOneColumn {
    
    public function processBoot()
    {
        $timeShift = 86400;
        $oldData = $this->data;
        $this->data = [];
        
        foreach ($oldData as $time => $rec) {
            $this->data[$time+$timeShift] = $rec;
        }
    }
}