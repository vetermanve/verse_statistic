<?php


namespace Verse\Statistic\Core;

use PHPUnit\Framework\TestCase;
use Verse\Statistic\Configuration\Grouping\BasicGroping;
use Verse\Statistic\Configuration\GroupingFactory;
use Verse\Statistic\Configuration\StatisticFactory;
use Verse\Statistic\Core\ExampleStats\ExampleVisitStatistic;
use Verse\Statistic\Core\Schema\FileBasedStatsSchema;

class CoreProcessingSchemaTest extends TestCase
{
    public function testFileStatsSchema () 
    {
        $context = new StatsContext();
        
        $context->groupingFactory = new GroupingFactory();
        $context->groupingFactory->addGroupingModel(BasicGroping::TYPE, new BasicGroping());
        
        $context->statisticFactory = new StatisticFactory();
        $context->statisticFactory->addStats(new ExampleVisitStatistic());
        
        $context->set(StatsContext::FILE_STATS_DIRECTORY, __DIR__.DIRECTORY_SEPARATOR.'test-stats');
        
        $container = new StatsContainer();
        
        $processor = new StatProcessor();
        $processor->setContext($context);
        $processor->setContainer($container);
        
        $processor->addSchema(new FileBasedStatsSchema());
        $processor->run();
        
        $this->assertNotEmpty($container->results);
        $this->assertCount($container->evensContainer->eventsCount() * 3, $container->results);
    }
}