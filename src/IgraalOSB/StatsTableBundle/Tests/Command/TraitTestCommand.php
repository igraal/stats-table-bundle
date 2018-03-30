<?php

namespace IgraalOSB\StatsTableBundle\Tests\Command;

use IgraalOSB\StatsTableBundle\Command\StatsTableCommandTraits;
use IgraalOSL\StatsTable\StatsTable;
use Symfony\Component\Console\Command\Command;

class TraitTestCommand extends Command
{
    use StatsTableCommandTraits;

    protected function configure()
    {
        $this
            ->setName('test:command')
            ->setDescription('Test command')
            ->stConfigure();
    }

    protected function doExecute()
    {
        return new StatsTable([[0, 1]], ['Zero', 'One']);
    }
}
