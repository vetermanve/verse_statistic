<?php


namespace Verse\Statistic\View\ColumnFunction;

use Verse\Statistic\View\ColumnData;

abstract class AbstractColumnFunction extends ColumnData {
    
    /**
     * @var ColumnData
     */
    protected $firstColumn;
    
    /**
     * @var ColumnData
     */
    protected $secondColumn;
    
    
    abstract protected function processBoot();
    
    /**
     * @return ColumnData
     */
    abstract protected function getPrimaryColumn();
    
    /**
     * @return ColumnData[]
     */
    abstract protected function allColumns();
    
    final public function boot()
    {
        if ($this->booted) {
            return parent::boot();   
        }
        
        foreach ($this->allColumns() as $column) {
            $column->boot();
        }
        
        $this->bindIn($this->getPrimaryColumn());
        $this->processBoot();
        
        $this->booted = true;
        
        return parent::boot();
    }
    
}