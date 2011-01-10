<?php

namespace Bundle\Liip\XsltBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class XsltExtension extends Extension
{
    /**
     * Loads the Xslt configuration.
     *
     * @param array            $config    An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function configLoad($config, ContainerBuilder $container)
    {
        if (!$container->hasDefinition('xslt')) {
            $loader = new XmlFileLoader($container, __DIR__.'/../Resources/config');
            $loader->load('xslt.xml');
        }

        $container->setParameter('xslt.options', array_replace($container->getParameter('xslt.options'), $config));
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
        return 'http://www.symfony-project.org/schema/dic/xslt';
    }

    public function getAlias()
    {
        return 'xslt';
    }
}
