<?php

namespace Liip\XsltBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

class LiipXsltExtension extends Extension
{
    /**
     * Loads the Xslt configuration.
     *
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = array_shift($configs);
        foreach ($configs as $tmp) {
            $config = array_replace_recursive($config, $tmp);
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('xslt.xml');

        $options = array_replace($container->getParameter($this->getAlias().'.options'), $config);
        $container->setParameter($this->getAlias().'.options', $options);
    }

    public function getAlias()
    {
        return 'liip_xslt';
    }
}
