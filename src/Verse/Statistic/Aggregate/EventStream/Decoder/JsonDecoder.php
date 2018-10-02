<?php

namespace Verse\Statistic\Aggregate\EventStream\Decoder;

class JsonDecoder extends DecoderProto
{

    public function decode(string $eventBody)
    {
        return json_decode($eventBody, true);
    }
}