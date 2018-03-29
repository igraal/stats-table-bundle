<?php

namespace IgraalOSB\StatsTableBundle\Tests\EventListener;

use Doctrine\Common\Annotations\AnnotationReader;
use IgraalOSB\StatsTableBundle\Configuration\StatsTableResult;
use IgraalOSB\StatsTableBundle\EventListener\StatsTableListener;
use IgraalOSB\StatsTableBundle\Tests\EventListener\Fixtures\FooController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class StatsTableListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ControllerListener
     */
    private $listener;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var StatsTableListener
     */
    private $statsTableListener;

    public function setUp()
    {
        $this->listener = new ControllerListener(new AnnotationReader());
        $this->statsTableListener = new StatsTableListener();

        new StatsTableResult(array());
    }

    public function tearDown()
    {
        $this->listener = null;
        $this->request  = null;
    }

    /**
     * @group debug
     */
    public function testAnnotationGetsFormat()
    {
        $this->request = $this->createRequest('json', null);
        $this->doTestRequest();
    }

    public function testAnnotationGetsFormatFromRequestUri()
    {
        $this->request = $this->createRequest(null, 'http://localhost/test.json');
        $this->doTestRequest();
    }

    public function testAnnotationGetsFormatFromRequestUriWithParameter()
    {
        $this->request = $this->createRequest(null, 'http://localhost/test.json?parameter=?1');
        $this->doTestRequest();
    }

    private function doTestRequest()
    {
        $controller = new FooController();

        $this->event = $this->getFilterControllerEvent(array($controller, 'barAction'), $this->request);
        $this->listener->onKernelController($this->event);
        $this->statsTableListener->onKernelController($this->event);

        $this->assertNotNull($this->getReadedStatsTable());
        $this->assertEquals('json', $this->getReadedStatsTable()->getFormat());
    }

    protected function getFilterControllerEvent($controller, Request $request)
    {
        /* @var \Symfony\Component\HttpKernel\Kernel $mockKernel */
        $mockKernel = $this
            ->getMockBuilder(\Symfony\Component\HttpKernel\Kernel::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        return new FilterControllerEvent($mockKernel, $controller, $request, HttpKernelInterface::MASTER_REQUEST);
    }

    protected function createRequest($format = null, $requestUri = null)
    {
        $request = Request::create($requestUri, 'GET', array(
                '_statstable' => null,
                '_format' => $format
            )
        );

        $request->attributes->set('_statstable', null);
        $request->attributes->set('_format', $format);

        return $request;
    }

    protected function getReadedStatsTable()
    {
        return $this->request->attributes->get('_statstable');
    }
}
