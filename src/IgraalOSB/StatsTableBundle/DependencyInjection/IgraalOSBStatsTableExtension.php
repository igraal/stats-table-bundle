<?php

namespace IgraalOSB\StatsTableBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class IgraalOSBStatsTableExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $this->addClassesToCompile(array('IgraalOSB\\StatsTableBundle\\Configuration\\StatsTableResponse'));
    }
}
