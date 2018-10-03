<?php


namespace Verse\Statistic\View\ColumnFunction;

class Concat extends AbstractTwoColumns {
    
    public function processBoot()
    {
        $this->title .=' concat';
        
        foreach ($this->data as $time => &$data) {
            $data .= isset($this->data[$time], $this->secondColumn->data[$time]) ? ' '.$this->secondColumn->data[$time] : ''; 
        }
    }
}