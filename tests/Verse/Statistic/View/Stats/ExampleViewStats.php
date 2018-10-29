<?php


namespace Verse\Statistic\View\Stats;


use Verse\Statistic\Configuration\Grouping\BasicGroping;
use Verse\Statistic\Configuration\Stats\AbstractStatistic;
use Verse\Statistic\View\ColumnData;
use Verse\Statistic\View\ColumnFunction\ExtractUnq;
use Verse\Statistic\View\ColumnFunction\Ratio;

class ExampleViewStats extends AbstractStatistic
{
    public const F_SHOW  = 'show';
    public const F_CLICK = 'click';

    public function getName()
    {
        return 'Example View Stats';
    }

    public function getId()
    {
        return 'ex-view-stats';
    }

    public function getGroupingIds()
    {
        return [
            BasicGroping::TYPE,
        ];
    }

    public function getViews()
    {
        $views = parent::getViews();
        $views['custom']['title']  = 'Custom view - ratios';
        $views['custom']['fields'] = [
            [
                'fields' => [self::F_CLICK, self::F_SHOW],
                'title' => 'Ratio Clicks to Shows',
                'format' => function(ColumnData $clicks, ColumnData $shows) {
                    return new Ratio($clicks, $shows);
                }
            ],
            [
                'fields' => [self::F_CLICK, self::F_SHOW],
                'title' => 'Ratio Unique Clicks to Unique Shows',
                'format' => function(ColumnData $clicks, ColumnData $shows) {
                    return new Ratio(new ExtractUnq($clicks), new ExtractUnq($shows));
                }
            ]
        ];

        return $views;
    }

}