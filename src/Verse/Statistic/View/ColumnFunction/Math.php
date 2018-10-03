<?php

namespace Verse\Statistic\View\ColumnFunction;

use Verse\Statistic\View\ColumnData;

class Math extends AbstractOneColumn {
    
    CONST DO_MINUS    = '-';
    CONST DO_PLUS     = '+';
    CONST DO_DIVISION = '/';
    CONST DO_MULTIPLY = '*';
    
    private $action; 
    private $value; 
    
    public function __construct(ColumnData $column, $action, $value)
    {
        $this->action = $action;
        $this->value = $value;
        
        parent::__construct($column);
    }
    
    public function processBoot()
    {
        switch ($this->action) {
            case self::DO_MINUS:
                $this->_processMinus();
                break;
            case self::DO_PLUS:
                $this->_processPlus();
                break;
            case self::DO_DIVISION:
                $this->_processDivision();
                break;
            case self::DO_MULTIPLY:
                $this->_processMultiply();
                break;
            default:
                break;
        }
    }
    
    private function _processMinus()
    {
        foreach ($this->data as $time => &$rec) {
            $rec -= $this->value;
        }
    }
    
    private function _processPlus()
    {
        foreach ($this->data as $time => &$rec) {
            $rec += $this->value;
        }
    }
    
    private function _processDivision()
    {
        foreach ($this->data as $time => &$rec) {
            $rec = round($rec/$this->value, 2);
        }
    }
    
    private function _processMultiply()
    {
        foreach ($this->data as $time => &$rec) {
            $rec *= $this->value;
        }
    }
}