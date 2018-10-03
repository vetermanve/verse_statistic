<?php

namespace Verse\Statistic\Core\ExampleStats;

use Verse\Statistic\Configuration\Grouping\BasicGroping;
use Verse\Statistic\Configuration\Stats\AbstractStatistic;

class ExampleVisitStatistic extends AbstractStatistic
{
    const ID = 'visits';
    
    const F_VISIT = 'user_visit';

    public function getName()
    {
        return 'Site Visits';
    }

    public function getId()
    {
        return self::ID;
    }
    
    public function getGroupingIds () 
    {
        return [
            BasicGroping::TYPE
        ];
    }
}