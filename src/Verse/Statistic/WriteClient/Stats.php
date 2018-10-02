<?php


namespace Verse\Statistic\WriteClient;


class Stats
{
    const ST_EVENT_NAME = 0;
    const ST_USER_ID    = 1;
    const ST_COUNT      = 2;
    const ST_TIME       = 3;
    const ST_CONTEXT    = 4;
    const ST_SCOPE_ID   = 5;
    
    /**
     * @var Transport\StatisticWriteClientTransportInterface
     */
    protected $transport;

    /**
     * @var Encoder\EncoderInterface
     */
    protected $encoder;


    /**
     * Отправить эвент с явной передачей userId и companyId
     *
     * @param $field
     * @param       $userId
     * @param int $scopeId
     * @param array $context
     * @param float|int $count
     * @param int|null $eventTime
     * 
     * @return $this|\Verse\Statistic\WriteClient\Stats
     */
    public function event($field, $userId, $scopeId = 0, array $context = [], float $count = 1, $eventTime = null) : self
    {
        $this->sendEventData($this->makeEventData($field, $userId, $scopeId, $context, $count, $eventTime));

        return $this;
    }
    
    public function sendEventData ($eventData) : bool 
    {
        return $this->transport->send($this->encoder->encode($eventData));
    }
    
    public function makeEventData($field, $userId, $scopeId = 0, array $context = [], $count = 1, $eventTime = null) : array 
    {
        $eventData = [
            self::ST_EVENT_NAME => $field,
            self::ST_USER_ID    => $userId,
            self::ST_COUNT      => $count,
            self::ST_TIME       => $eventTime ?? time(),
            self::ST_CONTEXT    => $context,
            self::ST_SCOPE_ID   => $scopeId
        ];
        
        return $eventData;
    }

    /**
     * @return Transport\StatisticWriteClientTransportInterface
     */
    public function getTransport() : Transport\StatisticWriteClientTransportInterface
    {
        return $this->transport;
    }

    /**
     * @param Transport\StatisticWriteClientTransportInterface $transport
     */
    public function setTransport(Transport\StatisticWriteClientTransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @return Encoder\EncoderInterface
     */
    public function getEncoder() : Encoder\EncoderInterface
    {
        return $this->encoder;
    }

    /**
     * @param Encoder\EncoderInterface $encoder
     */
    public function setEncoder(Encoder\EncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    
}