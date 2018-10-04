<?php


namespace Verse\Statistic\Core\Schema;


use Verse\Modular\ModularSchemaInterface;
use Verse\Statistic\Core\StatProcessor;
use Verse\Statistic\Core\StatsModuleProto;
use Verse\Statistic\Core\Strategy\Aggregate\LoadEventsFromFiles;

class LoadEventsFromFilesSchema extends StatsModuleProto implements ModularSchemaInterface
{

    /**
     * @param StatProcessor $processor
     */
    public function configure($processor)
    {
        $processor->addStrategy(new LoadEventsFromFiles(), $processor::SECTION_RUN);
    }
}