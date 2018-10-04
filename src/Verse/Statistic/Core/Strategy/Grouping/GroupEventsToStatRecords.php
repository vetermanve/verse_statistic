<?php


namespace Verse\Statistic\Core\Strategy\Grouping;


use Verse\Modular\ModularStrategyInterface;
use Verse\Statistic\Configuration\Grouping\AbstractGrouping;
use Verse\Statistic\Core\StatsModuleProto;

class GroupEventsToStatRecords extends StatsModuleProto implements ModularStrategyInterface
{
    /**
     * @var AbstractGrouping[]
     */
    private $groupingModels = [];

    
    public function prepare()
    {
        $groupingEventsIdx = [];
        
        // collecting all stats groupings and match events to groupings
        foreach ($this->container->statisticModels as $stats) {
            $groupingsIds = $stats->getGroupingIds();
            $eventsIdx = array_flip($stats->getFields());
            
            foreach ($groupingsIds as $groupingId) {
                if (!isset($groupingEventsIdx[$groupingId])) {
                    $groupingEventsIdx[$groupingId] = [];
                }
                
                $groupingEventsIdx[$groupingId] += $eventsIdx;
            }
        }
        
        foreach ($groupingEventsIdx as $groupingId => $eventsIdx) {
            $groupingModel = $this->context->groupingFactory->getGroupingModelById($groupingId);
            
            if ($groupingModel) {
                $groupingModel->setEventsIdx(array_combine(array_keys($eventsIdx), array_keys($eventsIdx)));
                $this->groupingModels[$groupingId] = $groupingModel; 
            }
        }
    }

    public function run()
    {
        foreach ($this->groupingModels as $groupingModel) {
            $groupingModel->setEventsContainer($this->container->evensContainer);
            $this->container->addData($groupingModel->getStatRecords());
        }
    }

    public function shouldProcess()
    {
        return $this->container->statisticModels && $this->context->groupingFactory;
    }
}