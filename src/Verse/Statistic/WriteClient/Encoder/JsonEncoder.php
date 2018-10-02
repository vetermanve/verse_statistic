<?php

namespace Verse\Statistic\WriteClient\Encoder;

class JsonEncoder implements EncoderInterface
{
    public function encode($eventData) : string 
    {
        return json_encode($eventData, JSON_UNESCAPED_UNICODE);
    }
    
    public function decode(string $eventPayload)
    {
        return json_decode($eventPayload, true);
    }
}