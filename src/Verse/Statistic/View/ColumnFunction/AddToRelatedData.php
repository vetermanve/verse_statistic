<?php


namespace Verse\Statistic\View\ColumnFunction;

class AddToRelatedData extends AbstractTwoColumns {

    public function processBoot()
    {
        $this->dataRelated = $this->secondColumn->data;
    }
}