<?php

namespace IgraalOSB\StatsTableBundle\Response;

use IgraalOSL\StatsTable\Dumper\DumperInterface;
use IgraalOSL\StatsTable\StatsTable;
use Symfony\Component\HttpFoundation\Response;

class StatsTableResponse extends Response
{
    /** @var StatsTable */
    private $statsTable;
    /** @var DumperInterface */
    private $dumper;

    public function __construct(StatsTable $statsTable, DumperInterface $dumper, $headers = array())
    {
        parent::__construct('', 200, $headers);

        $this->statsTable = $statsTable;
        $this->dumper = $dumper;

        if (!$this->headers->has('content-type')) {
            $this->headers->set('content-type', $dumper->getMimeType());
        }
    }

    /**
     * @param DumperInterface $dumper
     */
    public function setDumper($dumper)
    {
        $this->dumper = $dumper;
    }

    /**
     * @return DumperInterface
     */
    public function getDumper()
    {
        return $this->dumper;
    }

    /**
     * @param StatsTable $statsTable
     */
    public function setStatsTable($statsTable)
    {
        $this->statsTable = $statsTable;
    }

    /**
     * @return StatsTable
     */
    public function getStatsTable()
    {
        return $this->statsTable;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        // Initialize content if required
        if ('' === $this->content) {
            $this->content = $this->dumper->dump($this->statsTable);
        }

        return $this->content;
    }
}
