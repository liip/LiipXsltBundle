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

    protected $extensions = array();

    public function __construct(ContainerInterface $container, LoaderInterface $loader, $options = array())
    {
        $this->container = $container;
        $this->loader = $loader;

        if (isset($options['extensions'])) {
            foreach ($options['extensions'] as $extension) {
                $this->extensions[] = $this->container->get($extension);
            }
        }
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

        // Extensions
        foreach ($this->extensions as $extension) {
            $extension->apply($dom);
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
