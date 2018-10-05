<?php


namespace Verse\Statistic\WriteClient;


interface StatsClientInterface
{
    public function event($field, $userId, $scopeId = 0, array $context = [], float $count = 1, $eventTime = null);

    public function sendEventData ($eventData) : bool;

    public function makeEventData($field, $userId, $scopeId = 0, array $context = [], $count = 1, $eventTime = null);
}