<?php

namespace Bundle\Liip\XsltBundle;

use Symfony\Component\Templating\Renderer\Renderer as BaseRenderer;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;
use Symfony\Component\Templating\Storage\Storage;
use Symfony\Component\Templating\Storage\FileStorage;

class Renderer extends BaseRenderer
{
    protected $kernel;
    protected $request;
    protected $router;
    protected $options;

    public function __construct(Kernel $kernel, Request $request, Router $router, $options = array())
    {
        $this->kernel = $kernel;
        $this->request = $request;
        $this->options = $options;
        $this->router = $router;
    }

    /**
     * Evaluates a template.
     *
     * @param Storage $template   The template to render
     * @param array   $parameters An array of parameters to pass to the template
     *
     * @return string|false The evaluated template, or false if the renderer is unable to render the template
     */
    public function evaluate(Storage $template, array $parameters = array())
    {
        $dom = new \DOMDocument();

        if ($template instanceof FileStorage) {
            $dom->load($template);
        } else {
            $dom->loadXML($template->getContent());
        }

        $xsl = new \XSLTProcessor();
        $xsl->importStyleSheet($dom);

        $builder = new Builder($parameters);
        $dom = $builder->getDOM();

        // Environment
        $environment = array (
            'base_path' => $this->request->getBasePath(),
            'base_url' => $this->request->getBaseUrl(),
            'app_name' => $this->kernel->getName(),
            'env' => $this->kernel->getEnvironment(),
            'debug' => $this->kernel->isDebug() ? 'true' : 'false',
        );

        foreach ($environment as $name => $value) {
            $attr = $dom->createAttribute($name);
            $attr->appendChild($dom->createTextNode($value));
            $dom->documentElement->appendChild($attr);
        }

        // Routes
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

        // Debug mode
        if ($this->kernel->isDebug()) {

            // Check for ?XML parameter
            if ($this->request->query->get('XML', false) !== false) {

                // print raw XML
                header('Content-Type: text/xml');
                echo $dom->saveXML();
                exit;
            }
        }

        return $xsl->transformToXML($dom);
    }
}
