<?php


namespace Verse\Statistic\Core;


use Verse\Modular\ModularSystemModule;

class StatsModuleProto implements ModularSystemModule
{
    /**
     * @var StatsContainer;
     */
    protected $container;

    /**
     * @var StatsContext
     */
    protected $context;

    /**
     * @return StatsContainer
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param StatsContainer $container
     * @return $this
     */
    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @return StatsContext
     */
    public function getContext() : StatsContext
    {
        return $this->context;
    }

    /**
     * @param StatsContext $context
     *
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }
}