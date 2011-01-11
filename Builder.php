<?php

namespace Bundle\Liip\XsltBundle;

class Builder
{
    protected $dom;

    public function __construct($value)
    {
        $this->dom = new \DOMDocument();
        $root = $this->dom->createElement('page');
        $this->dom->appendChild($root);

        $this->parse($root, $value);
    }

    public function parse($parent, $value)
    {
        if (is_array($value)) {

            foreach ($value as $name => $childValue) {

                if (is_numeric($name)) {
                    $name = 'object'; // numberic node names are not valid, so use <object>
                }
                $child = $this->dom->createElement($name);
                $parent->appendChild($child);

                $this->parse($child, $childValue);
            }

        } elseif ($value instanceof \DOMNode) {
            $child = $this->dom->importNode($value, true);
            $parent->appendChild($child);
        } elseif ($value instanceof \SimpleXMLElement) {
            $node = dom_import_simplexml($value);
            $child = $this->dom->importNode($node, true);
            $parent->appendChild($child);
        } else {
            $text = $this->dom->createTextNode($value);
            $parent->appendChild($text);
        }
    }

    public function getDOM()
    {
        return $this->dom;
    }
}
