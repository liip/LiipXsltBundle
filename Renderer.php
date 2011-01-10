<?php

namespace Bundle\XsltBundle;

use Symfony\Component\Templating\Renderer\Renderer as BaseRenderer;
use Symfony\Component\Templating\Storage\Storage;
use Symfony\Component\Templating\Storage\FileStorage;

class Renderer extends BaseRenderer
{
    protected $options;

    public function __construct($options)
    {
        $this->options = $options;
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
        if ($template instanceof FileStorage)
        {
            $dom = new \DOMDocument();
            $dom->load($template);

        } else {

            $dom = new \DOMDocument();
            $dom->loadXML($template->getContent());
        }

        $xsl = new \XSLTProcessor();
        $xsl->importStyleSheet($dom);

        $dom = new \DOMDocument();

        $root = $dom->createElement('response');
        $root = $dom->appendChild($root);

        foreach ($parameters as $name => $value)
        {

            $parameter = $dom->createElement($name);
            $parameter = $root->appendChild($parameter);

            if ($value instanceof \DOMElement)
            {
                $child = $dom->importNode($value, true);
                $parameter->appendChild($child);
            }
            else
            {
                $text = $dom->createTextNode($value);
                $parameter->appendChild($text);
            }
        }

        // Debug mode
        if ($this->options['debug'] === true) {

            // Check for ?XML parameter
            $request = $this->engine->getContainer()->get('request');

            if ($request->query->get('XML', false) !== false) {

                // print raw XML
                header('Content-Type: text/xml');
                echo $dom->saveXML();
                exit;
            }
        }

        return $xsl->transformToXML($dom);
    }
}
