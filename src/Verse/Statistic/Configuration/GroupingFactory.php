<?php


namespace Verse\Statistic\Configuration;


use Verse\Statistic\Configuration\Grouping\AbstractGrouping;

class GroupingFactory
{
    protected $models = [];

    /**
     * @var callable
     */
    protected $groupingModelAutoloadCallback;

    /**
     * @param $groupingId
     * 
     * @return AbstractGrouping
     */
    public function getGroupingModelById ($groupingId) 
    {
        if (isset($this->models[$groupingId])) {
            return $this->models[$groupingId];
        }
        
        if (\is_callable($this->groupingModelAutoloadCallback)) {
            $model = ($this->groupingModelAutoloadCallback)($groupingId);
            if ($model instanceof AbstractGrouping) {
                $this->models[$groupingId] = $model; 
            }
        }
        
        return $this->models[$groupingId] ?? null;
    }
    
    public function addGroupingModel ($groupingId, AbstractGrouping $model) 
    {
        $this->models[$groupingId] = $model;
    }

    /**
     * @return mixed
     */
    public function getGroupingModelAutoloadCallback()
    {
        return $this->groupingModelAutoloadCallback;
    }

    /**
     * @param mixed $groupingModelAutoloadCallback
     */
    public function setGroupingModelAutoloadCallback($groupingModelAutoloadCallback)
    {
        $this->groupingModelAutoloadCallback = $groupingModelAutoloadCallback;
    }
}