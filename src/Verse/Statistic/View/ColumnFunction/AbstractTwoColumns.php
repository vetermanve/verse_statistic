<?php

namespace Verse\Statistic\View\ColumnFunction;

use Verse\Statistic\View\ColumnData;

abstract class AbstractTwoColumns extends AbstractColumnFunction {
    
    /**
     *
     * @param $firstColumn ColumnData
     * @param $secondColumn ColumnData
     */
    public function __construct($firstColumn, $secondColumn)
    {
        $this->firstColumn  = $firstColumn;
        $this->secondColumn = $secondColumn;
    }
    
    /**
     * @return ColumnData
     */
    protected function getPrimaryColumn()
    {
        return $this->firstColumn;
    }
    
    /**
     * @return ColumnData[]
     */
    protected function allColumns()
    {
        return [$this->firstColumn, $this->secondColumn];
    }
}