<?php

namespace Verse\Statistic\View\ColumnFunction;

use Verse\Statistic\View\ColumnData;

class AddText extends AbstractOneColumn {
    
    protected $prefix = '';
    protected $postfix = '';
    
    function __construct(ColumnData $column, $prefix = '', $postfix = '')
    {
        $this->postfix = $postfix;
        $this->prefix = $prefix;
        
        parent::__construct($column);
    }
    
    
    public function processBoot()
    {
        foreach ($this->data as $time => &$rec) {
            if ($rec === '') {
                continue;
            }
            
            $rec = $this->prefix.$rec.$this->postfix;
        }
    }
}