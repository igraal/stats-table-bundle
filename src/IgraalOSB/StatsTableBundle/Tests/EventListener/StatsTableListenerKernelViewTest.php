<?php

namespace IgraalOSB\StatsTableBundle\Tests\EventListener;

use IgraalOSB\StatsTableBundle\Configuration\StatsTableResult;
use IgraalOSB\StatsTableBundle\EventListener\StatsTableListener;
use IgraalOSL\StatsTable\StatsTable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class StatsTableListenerKernelViewTest extends \PHPUnit_Framework_TestCase
{
    /** @var StatsTableListener */
    protected $listener;

    protected function setUp()
    {
        parent::setUp();

        $this->listener = $this->getStatsTableListener();
    }

    protected function tearDown()
    {
        $this->listener = null;

        parent::tearDown();
    }


    public function testSimpleResponse()
    {
        $request = $this->getRequest(false);
        $event = $this->getGetResponseForControllerResultEvent($request);

        $this->listener->onKernelView($event);

        $this->assertNull($event->getResponse());
    }

    public function testJSONResponse()
    {
        $request = $this->getRequest(true, 'json');
        $statsTable = new StatsTable(array(array('what' => 'else ?')));
        $event = $this->getGetResponseForControllerResultEvent($request, $statsTable);

        $this->listener->onKernelView($event);

        $response = $event->getResponse();
        $this->assertNotNull($response);
        $this->assertContains('application/json', $response->headers->get('content-type'));

        $jsonArray = json_decode($response->getContent(), true);
        $this->assertInternalType('array', $jsonArray);
        $this->assertArrayHasKey('data', $jsonArray);
        $this->assertEquals(array(array('what' => 'else ?')), $jsonArray['data']);
    }

    public function testJSONResponseWithExistingResponse()
    {
        $request = $this->getRequest(true, 'json');
        $response = new Response();
        $response->headers->set('X-Custom', 'value');
        $statsTable = new StatsTable(array(array('what' => 'else ?')));
        $event = $this->getGetResponseForControllerResultEvent($request, $statsTable, $response);

        $this->listener->onKernelView($event);

        $response = $event->getResponse();
        $this->assertNotNull($response);
        $this->assertContains('application/json', $response->headers->get('content-type'));
        $this->assertEquals('value', $response->headers->get('X-Custom'));
    }

    /**
     * @return StatsTableListener
     */
    protected function getStatsTableListener()
    {
        return new StatsTableListener();
    }

    /**
     * @param  bool $enableStatsTable
     * @return Request
     */
    protected function getRequest($enableStatsTable = true, $_format = null)
    {
        $request = new Request();

        if ($enableStatsTable) {
            $statsTableResult = new StatsTableResult(array());
            $statsTableResult->setFormat($_format);

            $request->attributes->set(
                '_statstable',
                $statsTableResult
            );
        }

        return $request;
    }

    /**
     * @param  Request                             $request
     * @param  Response|StatsTable                 $controllerResult
     * @param  Response                            $response
     * @return GetResponseForControllerResultEvent
     */
    protected function getGetResponseForControllerResultEvent(Request $request, $controllerResult = null, Response $response = null)
    {
        $kernelInterface = $this
            ->getMockBuilder(\Symfony\Component\HttpKernel\HttpKernelInterface::class)
            ->getMockForAbstractClass();

        $event = new GetResponseForControllerResultEvent($kernelInterface, $request, HttpKernelInterface::MASTER_REQUEST, $controllerResult);
        if (null !== $response) {
            $event->setResponse($response);
        }

        return $event;
    }
}
