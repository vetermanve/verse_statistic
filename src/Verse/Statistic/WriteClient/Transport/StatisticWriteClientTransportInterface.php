<?php


namespace Verse\Statistic\WriteClient\Transport;


interface StatisticWriteClientTransportInterface
{
    public function send (string $payload) : bool; 
}