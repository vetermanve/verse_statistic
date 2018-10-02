<?php


namespace Verse\Statistic\Aggregate\EventStream;


use Verse\Router\Actors\RouterRequestConsumer;
use Verse\Statistic\Aggregate\EventStream\Event\EventStreamItem;

class AmqpRouterEventStream implements EventStreamInterface
{
    /**
     * @var \Verse\Router\Actors\RouterRequestConsumer
     */
    protected $consumer;
    
    public function get()
    {
        $item = $this->consumer->readOne();
        
        if ($item) {
            $event = new EventStreamItem();
            $event->body = $item;
            return $event;
        }
        
        return null;
    }

    public function acknowledgePosition()
    {
        return null;
    }

    /**
     * @return \Verse\Router\Actors\RouterRequestConsumer
     */
    public function getConsumer() : RouterRequestConsumer
    {
        return $this->consumer;
    }

    /**
     * @param \Verse\Router\Actors\RouterRequestConsumer $consumer
     */
    public function setConsumer(RouterRequestConsumer $consumer)
    {
        $this->consumer = $consumer;
    }
}