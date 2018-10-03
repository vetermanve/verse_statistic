<?php


namespace Verse\Statistic\View\ColumnFunction;

use Verse\Statistic\View\ColumnData;

abstract class AbstractOneColumn extends AbstractColumnFunction {

    function __construct(ColumnData $column)
    {
        $this->firstColumn = $column;
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
        return [$this->firstColumn];
    }
}