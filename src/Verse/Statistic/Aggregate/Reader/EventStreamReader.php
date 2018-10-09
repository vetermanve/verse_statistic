<?php


namespace Verse\Statistic\Aggregate\Reader;


use Verse\Statistic\Aggregate\EventsContainer;
use Verse\Statistic\Aggregate\EventStream\EventStreamInterface;

class EventStreamReader extends ReaderProto
{
    /**
     * @var EventStreamInterface
     */
    protected $eventStream;

    
    public function readChunk() : EventsContainer
    {
        $count = 0;
        
        while ($event = $this->eventStream->get()) {
            $this->eventsContainer->events[] = \is_string($event->body) && $this->_decoder 
                ? $this->_decoder->decode($event->body)
                : $event->body;
        
            if (++$count >= $this->chunkSize) {
                break;
            }
        }
        
        return $this->eventsContainer;
    }
    
    public function acknowledgeEvents () 
    {
        $this->eventStream->acknowledgePosition();
    }

    /**
     * @return EventStreamInterface
     */
    public function getEventStream() : EventStreamInterface
    {
        return $this->eventStream;
    }

    /**
     * @param EventStreamInterface $eventStream
     */
    public function setEventStream(EventStreamInterface $eventStream)
    {
        $this->eventStream = $eventStream;
    }
}