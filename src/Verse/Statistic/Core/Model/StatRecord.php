<?php


namespace Verse\Statistic\Core\Model;


class StatRecord
{
    public const SCOPE_ID   = 'scope_id';
    public const EVENT_ID   = 'event_id';
    public const GROUP_TYPE = 'group_type';
    public const GROUP_ID   = 'group_id';
    public const TIME_SCALE = 'time_scale';
    public const TIME       = 'time';
    public const COUNT      = 'cnt';
    public const COUNT_UNQ  = 'unq';

    public const TIME_RAW   = 'time_raw';
    public const UNIQUE_ID  = 'unique_id';

    public static function getRecordId($record)
    {
        return
            $record[self::SCOPE_ID].'.'.   // eg. 0 (global) 
            $record[self::EVENT_ID].'.'.   // eg. 1431234123 (crc32('visit')) 
            $record[self::GROUP_TYPE].'.'. // eg. 1 (group by user sex type = 1) 
            $record[self::GROUP_ID].'.'.   // eg. 2 (sex = female = 2)
            $record[self::TIME_SCALE].'.'. // eg. 0 (Hour time scale)
            $record[self::TIME];           // eg. 123412300 (12 sept 2018 16:00) 
    }
    
    public static function getRecordUniqId($record)
    {
        return 
            self::getRecordId($record).'.'.  //
            $record[self::UNIQUE_ID];        // eg. 3215 (User id = 3215)
    }
}