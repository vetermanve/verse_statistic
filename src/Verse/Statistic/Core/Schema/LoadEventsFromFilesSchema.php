<?php


namespace Verse\Statistic\Core\Schema;


use Verse\Modular\ModularSchemaInterface;
use Verse\Statistic\Core\StatProcessor;
use Verse\Statistic\Core\StatsModuleProto;
use Verse\Statistic\Core\Strategy\Aggregate\LoadEventsFromFiles;
use Verse\Statistic\Core\Strategy\Load\LoadKnownEventNamesAndHashes;
use Verse\Statistic\Core\Strategy\Load\LoadStatisticModels;

class LoadEventsFromFilesSchema extends StatsModuleProto implements ModularSchemaInterface
{

    /**
     * @param StatProcessor $processor
     */
    public function configure($processor)
    {
        $processor->addStrategy(new LoadStatisticModels(), $processor::SECTION_BEFORE);
        $processor->addStrategy(new LoadEventsFromFiles(), $processor::SECTION_BEFORE);
        $processor->addStrategy(new LoadKnownEventNamesAndHashes(), $processor::SECTION_BEFORE);
    }
}