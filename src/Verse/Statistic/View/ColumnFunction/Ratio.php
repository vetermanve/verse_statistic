<?php

namespace Verse\Statistic\View\ColumnFunction;

class Ratio extends AbstractTwoColumns {
    
    public function processBoot()
    {
        foreach ($this->data as $time => &$rec) {
            if (isset($this->secondColumn->data[$time])) {
//                $rec = round(($sourceData[$recordId]+($sourceData[$recordId]*$m))/$sourceData[$diffFRecordId]*10000-10000)/100;
                $rec = round($rec/$this->secondColumn->data[$time]*1000)/1000;
            } else {
                unset($this->data[$time]);
            }
        }
    }
    
}