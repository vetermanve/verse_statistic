<?php

namespace Verse\Statistic\Aggregate\EventStream;

interface EventStreamInterface
{
    public function get();
    public function acknowledgePosition();
}