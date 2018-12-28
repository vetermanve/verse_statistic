<?php

namespace Verse\Statistic\View;

use DateInterval;
use DatePeriod;
use DateTime;

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
        if (\is_array($fields)) {
            return $fields;
        } 
        
        if(\is_callable($fields)) {
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
        $orderId = 1;
        
        foreach ($this->currentViewData['fields'] as $formatData) {
            $viewFormatter = $formatData['format'];
            $fieldIds = $this->getFieldsIds($formatData['fields']);

            // get first field
            $firstField = \key($fieldIds);
            $firstFieldId = $fieldIds[$firstField];
            
            // if no first field data - skip
            if (!isset($this->sourceColumn->columnsByField[$firstFieldId])) {
                continue;
            }
            
            // processing always based on groups, include root data that just in root group
            // we get groups form first field for the start 
            $existedGroupIds = array_keys($this->sourceColumn->columnsByField[$firstFieldId]);
            
            foreach ($existedGroupIds as $groupId) {
                $columns = [];

                foreach ($fieldIds as $fieldId) {
                    if (!isset($this->sourceColumn->columnsByField[$fieldId][$groupId])) {
                        break;
                    }
    
                    $columns[] = $this->sourceColumn->columnsByField[$fieldId][$groupId];
                }

                // if no data for some of columns in this period we skip
                if (\count($columns) !== \count($fieldIds)) {
                    continue;
                }

                /* @var $resColumn ColumnData */
                // call formatting function
                $resColumn = \call_user_func_array($viewFormatter, $columns);  
                $resColumn->bindInData($formatData);
                $resColumn->order = $orderId++;
                $resColumn->group_id = $groupId;
                $resColumn->field = $firstField; 
                $resColumn->fieldId = $firstFieldId; 

                $this->columnPack->addColumn($resColumn);
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
    
    public function getTableData () : array
    {
        $table = [];
        foreach ($this->getRows() as $time) {
            $values = [];
            foreach ($this->columnPack->columns as $column) {
                $dataToTable = [];

                if (isset($column->data[$time])) {
                    $dataToTable[] = $column->data[$time];
                }

                if (isset($column->dataRelated[$time])) {
                    $dataToTable[] = $column->dataRelated[$time];
                }

                $values[] = $dataToTable;
            }

            $table[$time] = $values;
        }
        
        return $table;
    }
    
    public function getSeriesArrayData () : array
    {
        $times = \array_reverse($this->getRows());

        // Axes
        $seriesArray = [];
        foreach ($this->columnPack->columns as $key => $column) {
            if ($column->hideOnGraph) {
                continue;
            }

            $series = [];
            foreach ($times as $time) {
                $series[] =  [$time * 1000, $column->data[$time] ?? 0];
            }

            $seriesArray[] = [
                self::SERIES_NAME => $column->title . ($column->subTitle ? ' ' . $column->subTitle : ''),
                self::SERIES_DATA => $series
            ];
        }
        
        return $seriesArray;
    }
    
    public function getFieldsTitles () : array
    {
        $fieldsTitles = [];
        foreach ($this->columnPack->columns as $column) {
            $fieldsTitles[] = $column->title . ($column->subTitle ? ' ' . $column->subTitle : '');
        }
        
        return $fieldsTitles;
    }
    
    public function getResultData () : array
    {
        $data = [
            self::SCOPE_ID     => $this->scopeId,
            self::TITLE        => $this->currentViewData[self::TITLE] ,
            self::COLUMN_NAMES => $this->getFieldsTitles(),
            self::SERIES       => $this->getSeriesArrayData(),
            self::TABLE        => $this->getTableData(),
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

        $interval = TimeScale::INTERVAL[$this->timeScale];
        if ($shitType == self::SHIFT_ADD_PAST) {
            $fromTime -= $interval;    
        }

        if ($fromTime >= $toTime) {
            \trigger_error("From Time is grater than To Time, it's strange...", E_USER_WARNING);
            return [];
        }
        
        switch ($this->timeScale) {
            case TimeScale::HOUR:
                $fromTime  = floor($fromTime / 3600) * 3600;
                $toTime = ceil($toTime / 3600) * 3600;
                return range($toTime, $fromTime, $interval);
                break;
                
            case TimeScale::DAY:
                $fromTime  = mktime(0, 0, 0, date('m', $fromTime), date('d', $fromTime), date('Y', $fromTime));
                $toTime = mktime(0, 0, 0, date('m', $toTime), date('d', $toTime), date('Y', $toTime));

                return range($toTime, $fromTime, $interval);
                break;
                
            case TimeScale::MONTH:
                $fromTimeAligned  = mktime(0, 0, 0, date('m', $fromTime), 1, date('Y', $fromTime));
                $toTimeAligned = mktime(0, 0, 0, date('m', $toTime), 1, date('Y', $toTime));
                $start    = (new DateTime())->setTimestamp($fromTimeAligned);
                $end      = (new DateTime())->setTimestamp($toTimeAligned);
                
                $interval = DateInterval::createFromDateString('1 month');
                $period   = new DatePeriod($start, $interval, $end);
                
                /* @var $period DateTime[] */
                foreach ($period as $id => $dt) {
                    $results[] = $dt->getTimestamp();
                }

                $results[] = $period->getEndDate()->getTimestamp();
                
                return array_reverse($results);

            case TimeScale::WEEK:
                // get mktime for first day of $time's week
                // N - ISO-8601 number of the day of the week - 1 (for Monday) through 7 (for Sunday)
                $fromStartWeekDayOffset = date('N', $fromTime) - 1;
                $time = strtotime("-{$fromStartWeekDayOffset} days", $fromTime);
                $fromTimeAligned = (int)mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));

                $toStartWeekDayOffset = date('N', $toTime) - 1;
                $time = strtotime("-{$toStartWeekDayOffset} days", $toTime);
                $toTimeAligned = (int)mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));

                $start    = (new DateTime())->setTimestamp($fromTimeAligned);
                $end      = (new DateTime())->setTimestamp($toTimeAligned);

                $interval = DateInterval::createFromDateString('1 week');
                $period   = new DatePeriod($start, $interval, $end);

                /* @var $period DateTime[] */
                foreach ($period as $id => $dt) {
                    $results[] = $dt->getTimestamp();
                }

                $results[] = $period->getEndDate()->getTimestamp();

                return array_reverse($results);
        }
    
        return [];
    }
    
    public function getRowsNamed()
    {
        $res = [];
        $mask = $this->timeScale !== TimeScale::DAY ? 'd.m H:i' : 'd.m';
        switch ($this->timeScale) {
            case TimeScale::MONTH : 
                $mask = 'm/Y';
                break;
            case TimeScale::DAY :
            case TimeScale::WEEK :
                $mask = 'd.m.Y';
                break;
            case TimeScale::HOUR :
                $mask = 'd.m H:i';
                break;
        }
        
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
        } unset($fieldInfo);
        
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
