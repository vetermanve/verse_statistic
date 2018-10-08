<?php


namespace Verse\Statistic\Aggregate\Reader;

use Verse\Statistic\Aggregate\EventsContainer;
use Verse\Statistic\Aggregate\EventStream\Decoder\DecoderProto;

abstract class ReaderProto
{

    /**
     * Сколько мы выгребаем рекодов статистики из очереди за один запуск
     *
     * @var int
     */
    protected $chunkSize = 100;

    /**
     * @var EventsContainer
     */
    protected $eventsContainer;

    /**
     * @var DecoderProto
     */
    protected $_decoder;

    public function prepareCycle ()
    {
        $this->eventsContainer = new EventsContainer();
    }
    
    abstract public function readChunk();
    abstract public function acknowledgeEvents();

    /**
     * @param EventsContainer $eventsContainer
     */
    public function setEventsContainer($eventsContainer)
    {
        $this->eventsContainer = $eventsContainer;
    }

    /**
     * @return EventsContainer
     */
    public function getEventsContainer() : EventsContainer
    {
        return $this->eventsContainer;
    }
    
    /**
     * @return int
     */
    public function getChunkSize() : int
    {
        return $this->chunkSize;
    }

    /**
     * @param int $chunkSize
     */
    public function setChunkSize(int $chunkSize)
    {
        $this->chunkSize = $chunkSize;
    }

    /**
     * @return DecoderProto
     */
    public function getDecoder() : DecoderProto
    {
        return $this->_decoder;
    }

    /**
     * @param DecoderProto $decoder
     */
    public function setDecoder(DecoderProto $decoder)
    {
        $this->_decoder = $decoder;
    }
}