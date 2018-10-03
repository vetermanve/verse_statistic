<?php


namespace Verse\Statistic\View\ColumnFunction;

class ExtractUnq extends AbstractOneColumn {
    
    protected function processBoot()
    {
        $this->data = $this->dataUnq;
    }
}