<?php

namespace Verse\Statistic\View;

use Traversable;

/**
 * Class ColumnPack
 * 
 * Класс коллекции столбцов данных статистики
 * 
 * @package Statist
 */
class ColumnPack implements \Iterator {
    
    /**
     * @var ColumnData[]
     */
    public $columns = [];
    
    /**
     * @var ColumnData[][]
     */
    public $columnsByGroup = [];
    
    /**
     * @var ColumnData[][]
     */
    public $columnsByField = [];
    
    
    /**
     * @var ColumnData
     */
    public $sumColumn;
    /**
     * @var Dater
     */
    protected $dater;
    
    function __construct($dater)
    {
        $this->dater = $dater;
    }
    
    public function addColumn(ColumnData $column, $id = null)
    {
        
        if ($id) {
            $this->columns[$id] = $column;    
        } else {
            $this->columns[] = $column;
        }
        $column->columnPack = $this;
        
        $this->columnsByGroup[$column->group_id][$column->fieldId] = $column;
        $this->columnsByField[$column->fieldId][$column->group_id] = $column;
    }
    
    public function removeColumn(ColumnData $column)
    {
        
    }
    
    public function bindColumn($data)
    {
        // Example column
        /* 
        
         */
//      'group_type' => string '0' (length=1)
//      'field_id' => string '3960890811' (length=10)
//      'time_id' => string '1423602001' (length=10)
//      'group_id' => string '0' (length=1)
//      'cnt' => string '5228553' (length=7)
//      'unq' => string '23412' (length=5)
            
        $dater = $this->dater;
        $fieldsOrder = $dater->getFieldsOrder();
        $fieldsIdx = array_flip($dater->getFields());
        $grouping = $dater->getGrouping();
        $hideSubtitle = $dater->getGrouping()->isHideSubtitle();
        $names = $grouping->getDataRange();
            
        $keys = array_flip(array_keys($names));
            
        foreach ($data as $row) {
            $fId = $row['field_id'] . '.' . $row['group_id'];
    
            if (!isset($this->columns[$fId])) {
                $column = new ColumnData();
                
                $column->fieldId  = $row['field_id'];
                $column->group_id = $row['group_id'];
                $column->field    = $fieldsIdx[$column->fieldId];
                $column->title    = $fieldsIdx[$column->fieldId];
                $column->order    = ($fieldsOrder[$column->fieldId]+1)*10000 + @$keys[$column->group_id];
    
                if ($hideSubtitle) {
                    $column->subTitle = '';
                } else {
                    $column->subTitle = isset($names[$column->group_id]) ? $names[$column->group_id] : '#'.$column->group_id;
                }
                
                $this->addColumn($column, $fId);
            } else {
                $column = $this->columns[$fId];
            }
        
            $column->data[$row['time_id']]    = $row['cnt'];
            $column->dataUnq[$row['time_id']] = $row['unq'];
        }
        
    }
    
    
    public function boot () 
    {
        usort($this->columns, function (ColumnData $a,ColumnData  $b) {
            if ($a->order !== null && $b->order !== null) {
                return $a->order > $b->order ? 1 : -1;
            }
            
            return strnatcmp($a->title, $b->title);
        });
    
        foreach ($this->columnsByGroup as $grId => &$columnList) {
            usort($columnList, function (ColumnData $a,ColumnData  $b) {
                if ($a->order !== null && $b->order !== null) {
                    return $a->order > $b->order ? 1 : -1;
                }
                
                return strnatcmp($a->title, $b->title);
            });
            
            $prev = null;
            foreach ($columnList as $column) {
                if ($prev == null) {
                    $prev = $column;
                    continue;
                }
    
                $prev->rightColumn = $column;
                $prev = $column;
            }
        }
    
        foreach ($this->columns as $column) {
            $column->boot();
        }
        
    }
    
    public function minusRight () 
    {
    }
    
    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return current($this->columns);
    }
    
    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        next($this->columns);
    }
    
    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return key($this->columns);
    }
    
    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return is_object($this->current());
    }
    
    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        reset($this->columns);
    }
}
