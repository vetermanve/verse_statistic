<?php

namespace Verse\Statistic\WriteClient\Encoder;

interface EncoderInterface
{
    public function encode($eventData) : string;
    public function decode(string $eventPayload);
}