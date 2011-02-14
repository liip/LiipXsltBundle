<?php

namespace Liip\XsltBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

class XsltExtension extends Extension
{
    /**
     * Loads the Xslt configuration.
     *
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function configLoad($configs, ContainerBuilder $container)
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

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    public function getNamespace()
    {
        return 'http://www.liip.ch/schema/dic/xslt';
    }

    public function getAlias()
    {
        return 'liip_xslt';
    }
}
