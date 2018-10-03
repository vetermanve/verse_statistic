<?php


namespace Verse\Statistic\View\ColumnFunction;


class ClearRelated extends AbstractOneColumn{
    
    public function processBoot()
    {
        $this->dataRelated = []; 
    }
}