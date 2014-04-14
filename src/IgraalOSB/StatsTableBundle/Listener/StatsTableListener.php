<?php

namespace IgraalOSB\StatsTableBundle\Listener;

use IgraalOSB\StatsTable\Dumper;
use IgraalOSB\StatsTableBundle\Configuration;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class StatsTableListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    private $_types;

    protected function getTypes()
    {
        if (null === $this->_types) {
            $this->initTypes();
        }
        return $this->_types;
    }

    protected function initTypes()
    {
        $this->_types
            =
            array(
                array(
                    'formats' => array('xls'),
                    'class'   => 'IgraalOSL\StatsTable\Dumper\Excel\ExcelDumper',
                    'mime'    => 'application/vnd.ms-office; charset=binary',
                    'headers' => array('Content-Disposition' => 'attachment'),
                ),
                array(
                    'formats' => array('csv'),
                    'class'   => 'IgraalOSL\StatsTable\Dumper\CSV\CSVDumper',
                    'mime'    => 'text/csv; charset=utf-8',
                    'headers' => array('Content-Disposition' => 'attachment'),
                ),
                array(
                    'formats' => array('json'),
                    'class'   => 'IgraalOSL\StatsTable\Dumper\JSON\JSONDumper',
                    'mime'    => 'application/json',
                    'headers' => array()
                )
            );
    }

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Sets the format
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        $request = $event->getRequest();

        if (!$configuration = $request->attributes->get('_statstable')) {
            return;
        }

        if (!$format = $request->attributes->get('_format')) {
            return;
        }

        if (!$configuration instanceof Configuration\StatsTableResult) {
            return;
        }

        $configuration->setFormat($format);
    }

    /**
     * Dumps the statstable
     * @param  GetResponseForControllerResultEvent $event
     * @throws \RuntimeException
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $configuration = $event->getRequest()->attributes->get('_statstable');
        if (!$configuration instanceof Configuration\StatsTableResult) {
            return;
        }

        $types = $this->getTypes();
        $format = $configuration->getFormat();
        $formatConfiguration = array();
        foreach ($types as $type) {
            if (in_array($format, $type['formats'])) {
                $formatConfiguration = $type;
                break;
            }
        }

        if (0 === count($formatConfiguration)) {
            throw new \RuntimeException('Invalid format : "'.$format.'" given.');
        }

        $statsTable = $event->getControllerResult();
        if (!$statsTable instanceof \IgraalOSL\StatsTable\StatsTable) {
            throw new \RuntimeException('Response must be an instance of \\IgraalOSL\\StatsTable\\StatsTable');
        }

        $response = $event->getResponse();
        if (!$response) {
            $response = new Response();
        }
        $event->setResponse($response);

        /** @var \IgraalOSB\StatsTable\Dumper\DumperInterface $dumper */
        $dumper = new $formatConfiguration['class']();
        $content = $dumper->dump($statsTable);
        $response->setContent($content);

        $response->headers->set('Content-type', $formatConfiguration['mime']);
        foreach ($formatConfiguration['headers'] as $name => $value) {
            $response->headers->set($name, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => array('onKernelController', -128),
            KernelEvents::VIEW => 'onKernelView',
        );
    }
}
