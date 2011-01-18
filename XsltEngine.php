<?php

namespace Bundle\Liip\XsltBundle;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;
use Symfony\Component\Templating\Loader\LoaderInterface;
use Symfony\Component\Templating\Storage\FileStorage;

class XsltEngine implements EngineInterface
{
    protected $container;
    protected $loader;
    protected $kernel;
    protected $request;
    protected $router;
    protected $options;

    public function __construct(ContainerInterface $container, LoaderInterface $loader, Kernel $kernel, Request $request, Router $router, $options = array())
    {
        $this->container = $container;
        $this->loader = $loader;
        $this->kernel = $kernel;
        $this->request = $request;
        $this->options = $options;
        $this->router = $router;
    }

    /**
     * Renders a template.
     *
     * @param string $name       A template name
     * @param array  $parameters An array of parameters to pass to the template
     *
     * @return string The evaluated template as a string
     *
     * @throws \InvalidArgumentException if the template does not exist
     * @throws \RuntimeException         if the template cannot be rendered
     */
    public function render($name, array $parameters = array())
    {
        $dom = new \DOMDocument();

        $template = $this->load($name);

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

    /**
     * Returns true if the template exists.
     *
     * @param string $name A template name
     *
     * @return Boolean true if the template exists, false otherwise
     */
    public function exists($name)
    {
        try {
            $this->load($name);
        } catch (\Twig_Error_Loader $e) {
            return false;
        }

        return true;
    }

    /**
     * Loads the given template.
     *
     * @param string $name A template name
     *
     * @return Storage A Storage instance
     *
     * @throws \InvalidArgumentException if the template cannot be found
     */
    public function load($name)
    {
        $template = $this->loader->load($name);
        if (false === $template) {
            throw new \InvalidArgumentException(sprintf('The template "%s" does not exist.', $name));
        }

        return $template;
    }

    /**
     * Returns true if this class is able to render the given template.
     *
     * @param string $name A template name
     *
     * @return boolean True if this class supports the given resource, false otherwise
     */
    public function supports($name)
    {
        return false !== strpos($name, '.xsl');
    }

    /**
     * Renders a view and returns a Response.
     *
     * @param string   $view       The view name
     * @param array    $parameters An array of parameters to pass to the view
     * @param Response $response   A Response instance
     *
     * @return Response A Response instance
     */
    public function renderResponse($view, array $parameters = array(), Response $response = null)
    {
        if (null === $response) {
            $response = $this->container->get('response');
        }

        $response->setContent($this->render($view, $parameters));

        return $response;
    }
}
