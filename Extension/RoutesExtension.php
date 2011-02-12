<?php

namespace Liip\XsltBundle\Extension;

use Symfony\Component\Routing\Router;

class RoutesExtension implements Extension
{
    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function apply(\DOMDocument $dom, \XSLTProcessor $xslt)
    {
        $routes = $dom->createElement('routes');
        $dom->documentElement->appendChild($routes);
        foreach ($this->router->getRouteCollection()->all() as $name => $route) {

            $defaults = $route->getDefaults();
            $compiled = $route->compile();

            // Route node
            $node = $dom->createElement('route');
            $routes->appendChild($node);

            // Name attribute
            $attr = $dom->createAttribute('name');
            $attr->appendChild($dom->createTextNode($name));
            $node->appendChild($attr);

            // Tokens
            foreach ($compiled->getTokens() as $token) {

                $type = $token[0];

                $tokenNode = $dom->createElement('token');
                $attr = $dom->createAttribute('type');
                $attr->appendChild($dom->createTextNode($type));
                $tokenNode->appendChild($attr);
                $node->appendChild($tokenNode);

                if ('variable' === $type) {

                    $name = $token[3];

                    $attr = $dom->createAttribute('name');
                    $attr->appendChild($dom->createTextNode($name));
                    $tokenNode->appendChild($attr);


                    if (isset($defaults[$name])) {

                        $attr = $dom->createAttribute('default');
                        $attr->appendChild($dom->createTextNode($defaults[$name]));
                        $tokenNode->appendChild($attr);
                    }

                    $textNode = $dom->createTextNode($token[1]);
                    $tokenNode->appendChild($textNode);

                } elseif ('text' === $type) {

                    $textNode = $dom->createTextNode($token[1].$token[2]);
                    $tokenNode->appendChild($textNode);

                } else {
                    // TODO handle custom tokens
                }
            }
        }
    }
}
