<?php


namespace Verse\Statistic\Core\Strategy\Aggregate;


use Verse\Modular\ModularStrategyInterface;
use Verse\Statistic\Aggregate\EventStream\Decoder\JsonDecoder;
use Verse\Statistic\Aggregate\EventStream\FilesDirectoryEventStream;
use Verse\Statistic\Aggregate\Reader\EventStreamReader;
use Verse\Statistic\Core\StatsContext;
use Verse\Statistic\Core\StatsModuleProto;

class LoadEventsFromFiles extends StatsModuleProto implements ModularStrategyInterface
{

    /**
     * @var EventStreamReader
     */
    protected $reader;
    
    public function prepare()
    {
        $stream = new FilesDirectoryEventStream();
        $stream->setStatFilesDirectory($this->context->get(StatsContext::FILE_STATS_DIRECTORY, '/tmp/stats/'));
        
        $this->reader = new EventStreamReader();
        $this->reader->setDecoder(new JsonDecoder());
        $this->reader->setEventStream($stream);
        $this->reader->setChunkSize($this->context->get(StatsContext::READER_CHUNK_SIZE, 1000));
    }

    public function run()
    {
        $this->reader->prepareCycle();
        $this->reader->readChunk();
        
        $this->container->evensContainer = $this->reader->getEventsContainer();
        $this->container->evensContainer->reports[] = __METHOD__.' produced '. \count($this->container->evensContainer->events).' events';
    }

    public function shouldProcess()
    {
        return true;
    }
}