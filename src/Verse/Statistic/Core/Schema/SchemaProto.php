<?php


namespace Verse\Statistic\Core\Schema;


use Verse\Modular\ModularSchemaInterface;
use Verse\Statistic\Core\StatProcessor;

abstract class SchemaProto implements ModularSchemaInterface
{

    /**
     * @param StatProcessor $processor
     */
    abstract public function configure($processor);

}