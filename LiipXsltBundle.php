<?php

namespace Liip\XsltBundle;

use Liip\XsltBundle\DependencyInjection\XsltExtension;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LiipXsltBundle extends Bundle
{
    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    public function getPath()
    {
        return strtr(__DIR__, '\\', '/');
    }
}
