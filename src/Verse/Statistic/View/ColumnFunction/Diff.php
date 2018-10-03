<?php


namespace Verse\Statistic\View\ColumnFunction;

class Diff extends AbstractTwoColumns {
    
    public function processBoot()
    {
        foreach ($this->data as $time => &$rec) {
            if (isset($this->secondColumn->data[$time]) && $this->secondColumn->data[$time]) {
//                $rec = round(($sourceData[$recordId]+($sourceData[$recordId]*$m))/$sourceData[$diffFRecordId]*10000-10000)/100;
                $rec = round(($rec - $this->secondColumn->data[$time])*100)/100;
            } else {
                unset($this->data[$time]);    
            }
        }
    }
}