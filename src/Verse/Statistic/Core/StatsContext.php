<?php


namespace Verse\Statistic\Core;


use Verse\Modular\ModularContextProto;
use Verse\Statistic\Aggregate\EventStream\EventStreamInterface;
use Verse\Statistic\Aggregate\Reader\ReaderProto;
use Verse\Statistic\Configuration\GroupingFactory;
use Verse\Statistic\Configuration\StatisticFactory;
use Verse\Statistic\Storage\Records\StatRecordsStorageInterface;
use Verse\Statistic\Storage\Unique\UniqueStorageInterface;

class StatsContext extends ModularContextProto
{
    const EVENT_READER_CHUNK_SIZE = 'events-reader-chunk-size';
    
    const STORAGE_FILES_ROOT  = 'storage-files-root';

    const READER_CHUNK_SIZE = 'reader-chunk-size';

    /**
     * @var GroupingFactory
     */
    public $groupingFactory;

    /**
     * @var StatisticFactory
     */
    public $statisticFactory;

    /**
     * @var EventStreamInterface
     */
    public $eventsStream;
    
    /**
     * @var StatRecordsStorageInterface
     */
    public $statsStorage;

    /**
     * @var UniqueStorageInterface
     */
    public $uniqueStorage;
}