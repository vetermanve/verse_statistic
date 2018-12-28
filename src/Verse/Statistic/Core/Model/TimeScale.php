<?php


namespace Verse\Statistic\Core\Model;


class TimeScale
{
    public const RAW   = 0;
    public const HOUR  = 1;
    public const DAY   = 2;
    public const MONTH = 3;
    public const WEEK  = 4;

    public const INTERVAL = [
        self::HOUR  => 3600, // 60 min
        self::DAY   => 86400, // 24h
        self::MONTH => 2678400, //31 day
        self::WEEK  => 604800, //7 day
    ];
}