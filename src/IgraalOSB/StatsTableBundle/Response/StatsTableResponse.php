<?php

namespace IgraalOSB\StatsTableBundle\Response;

use IgraalOSL\StatsTable\Dumper\DumperInterface;
use IgraalOSL\StatsTable\StatsTable;
use Symfony\Component\HttpFoundation\Response;

class StatsTableResponse extends Response
{
    const ST_HEADER_CONTENT_DISPOSITION = 'content-disposition';
    const ST_HEADER_CONTENT_TYPE        = 'content-type';

    /** @var StatsTable */
    private $statsTable;
    /** @var DumperInterface */
    private $dumper;

    public function __construct(StatsTable $statsTable, DumperInterface $dumper, $headers = array())
    {
        parent::__construct('', 200, $headers);

        $this->statsTable = $statsTable;
        $this->dumper = $dumper;

        if (!$this->headers->has(self::ST_HEADER_CONTENT_TYPE)) {
            $this->headers->set(self::ST_HEADER_CONTENT_TYPE, $dumper->getMimeType());
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

    /**
     * Ask the browser to download the file with specific filename
     *
     * @param string $filename
     */
    public function askDownload($filename = 'stats')
    {
        $contentDisposition = 'attachement';
        if ($filename) {
            $contentDisposition .= sprintf('filename="%s"', addslashes($filename));
        }

        $this->headers->set(self::ST_HEADER_CONTENT_DISPOSITION, $contentDisposition);
    }

    public function noDownload()
    {
        $this->headers->remove(self::ST_HEADER_CONTENT_DISPOSITION);
    }
}
