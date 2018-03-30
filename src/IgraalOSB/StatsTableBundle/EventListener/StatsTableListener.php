<?php

namespace IgraalOSB\StatsTableBundle\EventListener;

use IgraalOSB\StatsTable\Dumper;
use IgraalOSB\StatsTableBundle\Configuration;
use IgraalOSB\StatsTableBundle\Response\StatsTableResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class StatsTableListener implements EventSubscriberInterface
{
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
        $this->_types = [
            [
                'formats' => ['xls'],
                'class'   => 'IgraalOSL\StatsTable\Dumper\Excel\ExcelDumper',
            ],
            [
                'formats' => ['csv'],
                'class'   => 'IgraalOSL\StatsTable\Dumper\CSV\CSVDumper',
            ],
            [
                'formats' => ['json'],
                'class'   => 'IgraalOSL\StatsTable\Dumper\JSON\JSONDumper',
            ]
        ];
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

        if (!$format = $this->getFormatFromRequest($request)) {
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
        $formatConfiguration = [];
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
            throw new \RuntimeException('Controller result must be an instance of \\IgraalOSL\\StatsTable\\StatsTable');
        }

        $response = $event->getResponse();
        if (!$response) {
            $response = new Response();
        }
        $event->setResponse($response);

        /** @var \IgraalOSB\StatsTable\Dumper\DumperInterface $dumper */
        $dumper = new $formatConfiguration['class']();

        $stResponse = new StatsTableResponse($statsTable, $dumper);
        $response->headers->set('Content-type', $stResponse->headers->get('content-type'));
        $response->setContent($stResponse->getContent());

        unset($stResponse);
    }

    /**
     * Retrieve format given URL
     * @param  Request     $request
     * @return null|string
     */
    protected function getFormatFromRequest(Request $request)
    {
        $format = $request->getRequestFormat(null);
        if ($format) {
            return $format;
        }

        // Guess given url
        $requestUri = $request->server->get('REQUEST_URI');
        $questionPos = strpos($requestUri, '?');
        $path = $questionPos !== false ? substr($requestUri, 0, $questionPos) : $requestUri;

        if (preg_match('@^.*\.(.*)$@', $path, $matchesExtension)) {
            return $matchesExtension[1];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', -128],
            KernelEvents::VIEW => 'onKernelView',
        ];
    }
}
