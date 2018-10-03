<?php


namespace Verse\Statistic\Configuration\Stats;


abstract class AbstractStatistic
{
    protected static $fields = [];

    abstract public function getName();
    abstract public function getId();
    abstract public function getGroupingIds();

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
}