<?php

namespace Liip\XsltBundle\Extension;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;

class EnvironmentExtension implements Extension
{
    protected $kernel;
    protected $request;

    public function __construct(Kernel $kernel, Request $request)
    {
        $this->kernel = $kernel;
        $this->request = $request;
    }

    public function apply(\DOMDocument $dom, \XSLTProcessor $xslt)
    {
        $environment = array (
            'base_path' => $this->request->getBasePath(),
            'base_url' => $this->request->getBaseUrl(),
            'app_name' => $this->kernel->getName(),
            'env' => $this->kernel->getEnvironment(),
            'debug' => $this->kernel->isDebug() ? 'true' : 'false',
        );

        $node = $dom->createElement('environment');
        $dom->documentElement->appendChild($node);

        foreach ($environment as $name => $value) {
            $child = $dom->createElement($name, $value);
            $node->appendChild($child);
        }
    }
}
