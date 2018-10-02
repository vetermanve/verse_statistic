<?php


namespace Verse\Statistic\WriteClient\Transport;


use Verse\Router\Router;

class AmqpRouterTransport implements StatisticWriteClientTransportInterface
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var string
     */
    protected $queueName;
    
    public function send(string $payload) : bool 
    {
        return (bool)$this->router->publish($payload, $this->queueName);
    }

    /**
     * @return Router
     */
    public function getRouter() : Router
    {
        return $this->router;
    }

    /**
     * @param Router $router
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @return string
     */
    public function getQueueName() : string
    {
        return $this->queueName;
    }

    /**
     * @param string $queueName
     */
    public function setQueueName(string $queueName)
    {
        $this->queueName = $queueName;
    }
}