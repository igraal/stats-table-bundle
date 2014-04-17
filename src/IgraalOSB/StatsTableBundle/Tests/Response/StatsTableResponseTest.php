<?php

namespace IgraalOSB\StatsTableBundle\Tests\Response;

use IgraalOSB\StatsTableBundle\Response\StatsTableResponse;
use IgraalOSL\StatsTable\Dumper\DumperInterface;
use IgraalOSL\StatsTable\StatsTable;

class StatsTableResponseTest extends \PHPUnit_Framework_TestCase
{
    /** @var DumperInterface */
    private $dumper;
    /** @var StatsTable */
    private $statsTable;

    protected function setUp()
    {
        parent::setUp();
        $this->dumper = $this->getDumperInterfaceMock();
        $this->statsTable = $this->getStatsTableMock();
    }

    protected function tearDown()
    {
        $this->dumper = null;
        $this->statsTable = null;
        parent::tearDown();
    }


    public function testResponseContentType()
    {
        $this->assertEquals('application/json', $this->getResponse()->headers->get('Content-type'));
    }

    public function testResponseContent()
    {
        $this->assertEquals('{"result":"ok"}', $this->getResponse()->getContent());
    }

    public function testAdditionalHeader()
    {
        $response = $this->getResponse(array('X-Custom' => 'customValue'));
        $this->assertEquals('customValue', $response->headers->get('X-Custom'));
    }

    public function testOverrideHeader()
    {
        $contentType = 'application/octet-stream; charset=custom';
        $response = $this->getResponse(array('Content-type' => $contentType));
        $this->assertEquals($contentType, $response->headers->get('Content-type'));
    }

    /**
     * @param  array              $headers
     * @return StatsTableResponse
     */
    public function getResponse(array $headers = array())
    {
        return new StatsTableResponse($this->statsTable, $this->dumper, $headers);
    }

    /**
     * @return StatsTable
     */
    protected function getStatsTableMock()
    {
        $statsTable = $this->getMock(
            'IgraalOSL\StatsTable\StatsTable',
            array(),
            array(array())
        );

        return $statsTable;
    }

    /**
     * @return DumperInterface
     */
    protected function getDumperInterfaceMock()
    {
        $dumper = $this->getMock('IgraalOSL\StatsTable\Dumper\DumperInterface');

        $dumper
            ->expects($this->any())
            ->method('getMimeType')
            ->will($this->returnValue('application/json'));

        $dumper
            ->expects($this->any())
            ->method('dump')
            ->will($this->returnValue(json_encode(array('result' => 'ok'))));

        return $dumper;
    }
}
