<?php

namespace Verse\Statistic\View;

use Verse\Statistic\Configuration\Grouping\AbstractGrouping;
use Verse\Statistic\Configuration\Stats\AbstractStatistic;
use Verse\Statistic\Core\Model\StatRecord;
use Verse\Statistic\Core\Model\TimeScale;

/**
 * Class Dater
 * 
 * @package Statistic
 */
class Dater {
    
    const SHIFT_ADD_FUTURE = 'future';
    const SHIFT_ADD_PAST = 'past';
    
    protected $fieldsOrder;
    private $fields;
    private $fromTime;
    private $toTime;
    private $filters;
    private $filter;
    private $oneField;
    private $timeScale = 'day';
    private $scopeId;
    
    /**
     * @var AbstractStatistic
     */
    private $statisticConfiguration;
    
    /**
     * @var AbstractGrouping
     */
    private $grouping;
    
    private $currentView = 'all';
    private $currentViewData = array(
        'type' => 'all',
    );
    
    protected $viewData = [];
    protected $viewFields = [];
    protected $skipGraph = [];
    protected $parts = [];
    protected $logs = [];
    protected $curTpl = 'column';
    
    /**
     * @var ColumnPack
     */
    protected $columnPack;
    
    /**
     * @var ColumnPack
     */
    protected $sourceColumn;
    
    function __construct()
    {
        $this->columnPack = new ColumnPack($this);
        $this->sourceColumn = new ColumnPack($this);
        
        $this->fromTime = strtotime('-7 day');
        $this->toTime = time();
    }

    public function startPart($name)
    {
        $this->parts[$name] = microtime(1);
    }

    public function stopPart ($name)
    {
        $this->logs[] = $name.': '.(isset ($this->parts[$name]) ? round((microtime(1) - $this->parts[$name]) * 1000) : '-1').'мс'
        ;
    }
    
    /**
     * @return mixed
     */
    public function getFields()
    {
        return $this->fields;
    }
    
    public function getFieldsOrder()
    {
        return $this->fieldsOrder;
    }
    
    public function setStatisticConfiguration(AbstractStatistic $statisticConfiguration) 
    {
        $this->statisticConfiguration = $statisticConfiguration;
    }
    
    /**
     * @param mixed $fields
     */
    public function setFields($fields)
    {
        $fields = (array) $fields;
        
        $resultFields = [];
        
        static $i = 0;
        
        foreach ($fields as $name => $field) {
            if (!is_numeric($field)) {
                $resultFields[$field] = \crc32($field);
                $this->fieldsOrder[$resultFields[$field]] = $i++;
            } else{
                $resultFields[$name] = $field;
                $this->fieldsOrder[$field] = $i++;
            }
        }
        
        $this->fields = $resultFields;
    }
    
    public function getTimeInterval() 
    {
        return $this->timeScale === 'day' ?  86400 : 3600;
    }
    
    public function getLoadFilter () 
    {
        //DB index: company_id, group_type, time_id, field_id, group_id
        $filter = 
            [
                StatRecord::SCOPE_ID   => $this->scopeId,
                StatRecord::GROUP_TYPE => $this->grouping->getId(),
                StatRecord::TIME       => $this->getRows(),
                StatRecord::EVENT_ID   => array_values($this->fields),
            ];
        
        if (is_numeric($this->groupInnerId)) {
            $filter[StatRecord::GROUP_ID] = $this->groupInnerId;
        }
            
        return $filter;
    }
    
    public function setRawData ($rawData) 
    {
        $this->sourceColumn->bindColumn($rawData);   
    }
    
    public function processView ()
    {
        $this->startPart('Обработка данных');
        
        if ($this->currentViewData['type'] === 'resolutionOne') {
            $this->curTpl = 'resolution';
        }
        
        $this->buildViewData();
        
        $this->stopPart('Обработка данных');
    }
    
    private function _parseFields($fields) {
        if (is_array($fields)) {
            return $fields;
        } else if(is_callable($fields)) {
            return call_user_func($fields);
        }
        
        return [$fields];
    }
    
    private function getFieldsIds($fields) {
        $fieldsIds = [];
    
        foreach ($fields as $k => $field) {
            $fieldsIds[$k] = \crc32($field);
        }
        
        return $fieldsIds;
    }
    
    /**
     *
     */
    public function buildViewData()
    {
        $this->viewData = [];
        $this->defGrType = @$this->currentViewData['gr'];
        
        foreach ($this->currentViewData['fields'] as $id => $formatData) {
            $viewFormatter = $formatData['format'];
            $fieldsIds = $this->getFieldsIds($formatData['fields']);
            
            $firstFieldId = reset($fieldsIds);
            $objId = $id;
            
            $eachSingleFieldFormat = isset($formatData['applyAllFields']);
            
            $mainFieldsIds = $eachSingleFieldFormat ? $fieldsIds : [$firstFieldId];
            
            foreach ($mainFieldsIds as $fieldId) {
    
                $fieldsPassingToFormat = $eachSingleFieldFormat ? [$fieldId] : $fieldsIds;
                
                if (!isset($this->sourceColumn->columnsByField[$fieldId])) {
                    continue;
                }
                
                reset($this->sourceColumn->columnsByField[$fieldId]);
                $existedGroupers = array_keys($this->sourceColumn->columnsByField[$fieldId]); 
                
                foreach ($existedGroupers as $grouperId) {
                    $columnsPassingToFormat = [];
    
                    foreach ($fieldsPassingToFormat as $toFormatFieldId) {
                        if (!isset($this->sourceColumn->columnsByField[$toFormatFieldId][$grouperId])) {
                            break;
                        }
        
                        $columnsPassingToFormat[] = $this->sourceColumn->columnsByField[$toFormatFieldId][$grouperId];
                    }
    
                    if (count($columnsPassingToFormat) != count($fieldsPassingToFormat)) {
                        continue;
                    }
    
                    $resColumn = call_user_func_array($viewFormatter, $columnsPassingToFormat); // в этом месте вызывается космос
                    $resColumn->objId = $objId;
                    /* @var $resColumn ColumnData */
                    $resColumn->bindInData($formatData);
                    $resColumn->copyFields(reset($columnsPassingToFormat), [StatRecord::GROUP_ID, StatRecord::EVENT_ID, 'field', 'order']);
    
                    $this->columnPack->addColumn($resColumn);
                }
            }
        }
        
        $this->columnPack->boot();
    }

    const TITLE        = 'title';
    const SERIES_DATA  = 'data';
    const DATE         = 'date';
    const VALUES       = 'values';
    const SERIES_NAME  = 'name';
    const SERIES       = 'series';
    const TABLE        = 'table';
    const SCOPE_ID     = 'scope_id';
    const COLUMN_NAMES = 'column_names';
    
    public function getResultData () 
    {
        $columnPack = $this->getColumnPack();
        $fieldsTitle = [];
        foreach ($columnPack->columns as $column) {
            $fieldsTitle[] = $column->title . ($column->subTitle ? ' ' . $column->subTitle : '');
        }

        $axData   = [];
        $timeTable = [];

        $times = $this->getRowsNamed();
        foreach ($times as $time => $dateName) {
            $values = [];
            foreach ($columnPack->columns as $column) {
                if (!$column->hideOnGraph) {
                    $key = $column->fieldId . '.' . $column->group_id;
                    $axData[$key][self::TITLE] = $column->title;
                    $axData[$key][self::SERIES_DATA][] = isset($column->data[$time]) ? [
                        $time * 1000,
                        (float)$column->data[$time]
                    ] : [$time * 1000, 0];
                }
                
                $dataToTable = [];
                if(isset($column->data[$time])) {
                    $dataToTable[] = $column->data[$time];
                }
                if(isset($column->dataRelated[$time])) {
                    $dataToTable[] = $column->dataRelated[$time];
                }
                $values[] = $dataToTable;
            }

            // $timeTable[] = [
            //     self::VALUES => $values,
            //     self::DATE   => $time,
            // ];
            $timeTable[$time] = $values;
        }

        $seriesArray = [];
        foreach ($axData as $key => $values) {
            $seriesArray[] = [
                self::SERIES_NAME => $values[self::TITLE],
                self::SERIES_DATA => array_reverse($values[self::SERIES_DATA]),
            ];
        }

        $viewData = $this->getCurrentViewData();
        
        $data = [
            self::SCOPE_ID     => $this->scopeId,
            self::TITLE        => $viewData[self::TITLE] ,
            self::COLUMN_NAMES => $fieldsTitle,
            self::SERIES       => $seriesArray,
            self::TABLE        => $timeTable,
        ];

        return $data;
    }
    
    /**
     * @return mixed
     */
    public function getFilters()
    {
        return $this->filters;
    }
    
    /**
     * @param mixed $filters
     */
    public function setFilters($filters)
    {
        $this->filters = (array)$filters;
    }
    
    /**
     * @return mixed
     */
    public function getFilter()
    {
        return $this->filter;
    }
    
    /**
     * @param mixed $filter
     */
    public function setFilter($filter)
    {
        $this->filter = (array)$filter;
    }
    
    /**
     * @return AbstractStatistic
     */
    public function getStatisticConfiguration() : AbstractStatistic
    {
        return $this->statisticConfiguration;
    }
    
    public function getRows($shitType = null)
    {
        $toTime = $this->toTime;
        $fromTime = $this->fromTime;
        
        if ($this->timeScale === TimeScale::DAY) {
            $shitPoint = 86400;
            $shiftValue = ($shitType === null) 
                    ? 0 
                    : ($shitType === self::SHIFT_ADD_PAST ? $shitPoint : -$shitPoint);
            
            $fromTime = $fromTime - $shiftValue;
            
            $saveTimeZone = date_default_timezone_get();
            date_default_timezone_set('UTC');
            $start  = mktime(0, 0, 0, date('m', $fromTime), date('d', $fromTime), date('Y', $fromTime));
            $toTime = mktime(0, 0, 0, date('m', $toTime), date('d', $toTime), date('Y', $toTime));
            date_default_timezone_set($saveTimeZone);
        } else {
            $shitPoint = 3600;
            $shiftValue = ($shitType === null)
                ? 0
                : ($shitType === self::SHIFT_ADD_PAST ? $shitPoint : -$shitPoint);
    
            $fromTime = $fromTime - $shiftValue;
            
            $start  = floor(($fromTime) / 3600) * 3600;
            $toTime = ceil($toTime / 3600) * 3600;
        }
    
        return array_reverse(range($start, $toTime, $this->getTimeInterval()));
    }
    
    public function getRowsNamed()
    {
        $res = [];
        $mask = $this->timeScale !== TimeScale::DAY ? 'd.m H:i' : 'd.m';
        foreach ($this->getRows(self::SHIFT_ADD_PAST) as $time) {
            $res[$time] = date($mask, $time);       
        }
        
        return $res;
    }
    
    public function setGrouping($grouping)
    {
        $this->grouping = $grouping;
    }
    
    public function getFieldsByParams()
    {
        $fields = [];
        foreach ($this->grouping->getDataRange() as $grId => $digTitle) {
            foreach ($this->fields as $title => $fdd) {
                $fields[$title.($digTitle ? '-'.$digTitle : '')] = $fdd.'.'.$grId;
            }
        }
        
        return $fields;
    }
    
    /**
     * @return AbstractGrouping
     */
    public function getGrouping()
    {
        return $this->grouping;
    }
    
    /**
     * @return string
     */
    public function getTimeScale()
    {
        return $this->timeScale;
    }
    
    /**
     * @param string $timeScale
     */
    public function setTimeScale($timeScale)
    {
        $this->timeScale = $timeScale;
    }
    
    /**
     * @return array
     */
    public function getViewFields()
    {
        return $this->viewFields;
    }
    
    /**
     * @return array
     */
    public function getViewData()
    {
        return $this->viewData;
    }
    
    protected $defGrType = 'line';
    
    public function getDefaultGraphType()
    {
        return $this->defGrType; // ? $this->defGrType  : $this->statisticConfiguration->getChartType();
    }
    
    /**
     * @return array
     */
    public function getSkipGraph()
    {
        return $this->skipGraph;
    }
    
    /**
     * @return string
     */
    public function getCurrentView()
    {
        return $this->currentView;
    }
    
    /**
     * @param string $viewId
     * 
     * @return $this
     */
    public function setCurrentView($viewId = null)
    {
        $views  = $this->statisticConfiguration->getViews();
        $viewId = $viewId && isset($views[$viewId]) ? $viewId : key($views); 
        
        $this->currentView     = $viewId;
        $this->currentViewData = $views[$viewId] + $this->currentViewData;

        $fields = array();
        
        foreach ($this->currentViewData['fields'] as &$fieldInfo) {
            $fieldInfo['fields'] = $this->_parseFields($fieldInfo['fields']);
            $fields = array_merge($fields, $fieldInfo['fields']);
        }
        
        $this->setFields($fields);
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getCurrentViewData()
    {
        return $this->currentViewData;
    }
    
    /**
     * @return ColumnPack
     */
    public function getColumnPack()
    {
        return $this->columnPack;
    }
    
    /**
     * @return array
     */
    public function getLogs()
    {
        return $this->logs;
    }
    
    protected $groupInnerId;
    protected $groupInnerName;
    
    /**
     * @return mixed
     */
    public function getGroupInnerId()
    {
        return $this->groupInnerId;
    }
    
    /**
     * @return mixed
     */
    public function isGroupInnerSet()
    {
        return is_numeric($this->groupInnerId);
    }
    
    /**
     * @param mixed $groupInnerId
     */
    public function setGroupInnerId($groupInnerId)
    {
        $this->groupInnerId = $groupInnerId;
        if($this->grouping) {
            $range = $this->grouping->getDataRange();
            $this->groupInnerName = isset($range[$this->groupInnerId]) ? $range[$this->groupInnerId] : 'что-то';
        }
    }
    
    /**
     * @return mixed
     */
    public function getGroupInnerName()
    {
        return $this->groupInnerName;
    }
    
    /**
     * @return ColumnPack
     */
    public function getSourceColumn()
    {
        return $this->sourceColumn;
    }
    
    /**
     * @return mixed
     */
    public function getOneField()
    {
        return $this->oneField;
    }
    
    /**
     * @param mixed $oneField
     */
    public function setOneField($oneField)
    {
        $this->oneField = $oneField;
    }
    
    /**
     * @return string
     */
    public function getCurTpl()
    {
        return $this->curTpl;
    }
    
    /**
     * @return mixed
     */
    public function getFromTime()
    {
        return $this->fromTime;
    }
    
    /**
     * @param mixed $fromTime
     */
    public function setFromTime($fromTime)
    {
        $this->fromTime = $fromTime;
    }
    
    /**
     * @return mixed
     */
    public function getToTime()
    {
        return $this->toTime;
    }
    
    /**
     * @param mixed $toTime
     */
    public function setToTime($toTime)
    {
        $this->toTime = $toTime;
    }
    
    /**
     * @return mixed
     */
    public function getScopeId()
    {
        return $this->scopeId;
    }
    
    /**
     * @param mixed $scopeId
     */
    public function setScopeId($scopeId)
    {
        $this->scopeId = $scopeId;
    }
}
