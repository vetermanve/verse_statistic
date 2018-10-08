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
    protected $_reader;
    
    public function prepare()
    {
        $this->_reader = new EventStreamReader();
        $this->_reader->setDecoder(new JsonDecoder());
        $this->_reader->setEventStream($this->context->eventsStream);
        $this->_reader->setChunkSize($this->context->get(StatsContext::READER_CHUNK_SIZE, 1000));
    }

    public function run()
    {
        $this->_reader->prepareCycle();
        $this->_reader->readChunk();
        
        $this->container->evensContainer = $this->_reader->getEventsContainer();
        $this->container->evensContainer->reports[] = __METHOD__.' produced '. \count($this->container->evensContainer->events).' events';
    }

    public function shouldProcess()
    {
        return $this->context->eventsStream;
    }
}