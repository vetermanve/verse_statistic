<?php

namespace Verse\Statistic\Aggregate\EventStream\Decoder;

abstract class DecoderProto
{
    abstract public function decode(string $message);
}