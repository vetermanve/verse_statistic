<?php


namespace Verse\Statistic\Configuration\Stats;

use Verse\Statistic\View\ColumnFunction\AddToRelatedData;
use Verse\Statistic\View\ColumnFunction\ExtractUnq;
use Verse\Statistic\View\Graph;

abstract class AbstractStatistic
{
    protected static $fields = [];

    abstract public function getName();
    abstract public function getId();
    abstract public function getGroupingIds();
    
    public function getFieldNames () 
    {
        return [];
    }

    public function getFields()
    {
        $class = get_called_class();

        if (!isset(self::$fields[$class])) {
            self::$fields[$class] = [];
            $const = (new \ReflectionClass($this))->getConstants();

            foreach ($const as $name => $value) {
                if (strpos($name, 'F_') === 0) {
                    self::$fields[$class][] = $value;
                }
            }
        }

        return self::$fields[$class];
    }
    
    public function getViews () 
    {
        $simpleFormat = function ($column) { return new AddToRelatedData($column, new ExtractUnq($column)); };
        
        $names = $this->getFieldNames();
        
        $views = [];
        $fields = [];
        foreach ($this->getFields() as $field) { 
            $fields[] = [
                'fields' => [$field],
                'title' => $names[$field] ?? $field,
                'format' => $simpleFormat
            ];
        }

        // Общее состояние по компании
        $views['default'] = array(
            'title' => 'Default view',
            'fields' => $fields,
            'gr' => Graph::GR_LINE,
        );

        return $views; 
    }
}