<?php

namespace Verse\Statistic\View;

/**
 * Class Graph
 * 
 * Класс отвечающий за настройку графиков выводимых на фронтенде с бекенда
 * пока что тут только константы
 * 
 * @package Statist
 */
class Graph {
    
    const GR_AREA   = 'area';
    const GR_LINE   = 'line';
    const GR_SPLINE = 'spline';
    const GR_AREA_PERCENT = 'area_percent';
    
    public static function getTypes()
    {
        return [
            'когорты'          => self::GR_AREA,
            'когорты-проценты' => self::GR_AREA_PERCENT,
            'линии'            => self::GR_LINE,
            'кривые'           => self::GR_SPLINE,
        ];
    }
}
