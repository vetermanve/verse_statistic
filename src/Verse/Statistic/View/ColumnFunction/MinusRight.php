<?php


namespace Verse\Statistic\View\ColumnFunction;

use Verse\Statistic\View\ColumnData;

class MinusRight extends AbstractOneColumn {
    
    public function processBoot()
    {
        if (!isset($this->rightColumn)) {
            return;
        }
        
        $this->rightColumn->boot();
        
        foreach ($this->data as $time => &$rec) {
            if (isset($this->rightColumn->data[$time]) && $this->rightColumn->data[$time]) {
                $rec = ($rec - $this->rightColumn->dataRelated[$time]);
            }
        }
    }
}