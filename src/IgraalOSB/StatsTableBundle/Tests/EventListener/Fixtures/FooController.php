<?php

namespace IgraalOSB\StatsTableBundle\Tests\EventListener\Fixtures;

use IgraalOSB\StatsTableBundle\Configuration\StatsTableResult;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class FooController
{
    /**
     * @StatsTableResult
     */
    public function barAction()
    {
    }
}
