<?php

namespace Liip\XsltBundle\Extension;

use Symfony\Component\Routing\Router;

class CallbackExtension implements Extension
{
    protected static $router;

    public function __construct(Router $router)
    {
        self::$router = $router;
    }

    public function apply(\DOMDocument $dom, \XSLTProcessor $xslt)
    {
        $xslt->registerPHPFunctions('Liip\XsltBundle\Extension\CallbackExtension::path');
    }

    /**
     * Flattens arrays and finds first node value of DOMElement.
     */
    protected static function normalize($data) {

        if ($data instanceof \DOMElement) {
            return $data->nodeValue;
        }

        if (is_array($data)) {
            foreach ($data as &$element) {
                return self::normalize($element);
            }
        }

        return $data;
    }

    public static function path($name) {

        $args = func_get_args();

        // remove $name
        array_shift($args);

        // transform every pair to key/value, e.g. array('id', 'foo') to array('id' => 'foo')
        $parameters = array();
        for($i = 1; $i < count($args); $i += 2) {
            $parameters[$args[$i - 1]] = self::normalize($args[$i]);
        }

        return self::$router->generate($name, $parameters);
    }
}
