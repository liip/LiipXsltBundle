<?php

namespace Bundle\Liip\XsltBundle;

/**
 * Turns an array of values into a DomDocument
 *
 */
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

    /**
     * @return DomDocument
     */
    public function getDOM()
    {
        return $this->dom;
    }

    /**
     * Handy little function to validate a string to be used for a XML node
     *
     * @param  $strName
     * @return bool
     */
    public static function isElementNameValid($strName)
    {
        // Element name validation
        if (strpos($strName, ' ') !== False || $strName == '') {
            // Bad key name, skip this entry
            return false;
        }
        //If key is not alpha numeric
        if (!preg_match('|^\w+$|', $strName)) {
            // Bad key name, skip this entry
            return false;
        }

        return true;
    }

    /**
     * Parse the array of variables and convert them to DomElements
     *
     * @param  $parentNode DomElement
     * @param  $arr array
     * @return bool
     */
    protected function parse($parentNode, $arr)
    {
        if (!is_array($arr)) {
            return false;
        }
        $append = true;
        foreach ($arr as $key => $data) {
            /**
             * Is this an array, and not a numeric key?
             */
            if (is_array($data) && is_numeric($key) !== true) {
                /**
                 * Is this array fully numeric keys?
                 */
                if (array_values($data) === $data) {
                    /**
                     * Create nodes to append to $parentNode based on the $key of this array
                     * Produces <xml><item>0</item><item>1</item></xml>
                     * From array("item" => array(0,1));
                     */
                    foreach ($data as $subdata) {
                        $append = $this->appendNode($parentNode, $subdata, $key);
                    }
                }
                else {
                    $append = $this->appendNode($parentNode, $data, $key);
                }
            }
                /**
                 * This test will happen if an array has mixed numeric keys and alphanumeric keys
                 */
            elseif (is_numeric($key)) {
                $append = $this->appendNode($parentNode, $data, "object");
            }
            /**
             * The simplest call. Add some text to the parent
             */
            elseif (self::isElementNameValid($key)) {
                $append = $this->appendNode($parentNode, $data, $key);
            }
        }
        return $append;
    }

    /**
     * Selects the type of node to create and appends it to the parent.
     *
     * @param  $parentNode
     * @param  $data
     * @param  $nodename
     * @return void
     */
    protected function appendNode($parentNode, $data, $nodeName)
    {
        $node = $this->dom->createElement($nodeName);
        $appendNode = $this->selectNodeType($node, $data);
        /**
         * We may have decided not to append this node, either in error or if its $nodename is not valid
         */
        if ($appendNode) {
            $parentNode->appendChild($node);
        }
        return $appendNode;
    }

    /**
     * Here we test the value being passed and decide what sort of element to create
     *
     * @param  $node
     * @param  $val
     * @return bool
     */
    protected function selectNodeType($node, $val)
    {
        $append = true;
        /**
         * Ah ha, an array. Let's recurse
         */
        if (is_array($val)) {
            $append = $this->parse($node, $val);
        }
        elseif (is_numeric($val)){
            $append = $this->appendText($node, $val);
        }
        elseif (is_string($val)){
            $append = $this->appendCData($node, $val);
        }
        elseif (is_bool($val)){
            $append = $this->appendText($node, intval($val));
        }
        elseif ($val instanceof \DOMNode){
            $child = $this->dom->importNode($val, true);
            $node->appendChild($child);
        }
        elseif ($val instanceof \SimpleXMLElement){
            $node = dom_import_simplexml($val);
            $child = $this->dom->importNode($node, true);
            $node->appendChild($child);
        }

        return $append;
    }

    /**
     * @param  $node
     * @param  $val
     * @return bool
     */
    protected function appendXMLString($node, $val)
    {
        if (strlen($val) > 0) {
            $frag = $this->dom->createDocumentFragment();
            $frag->appendXML($val);
            $node->appendChild($frag);
            return true;
        }

        return false;
    }

    /**
     * @param  $node
     * @param  $val
     * @return bool
     */
    protected function appendText($node, $val)
    {
        $nodeText = $this->dom->createTextNode($val);
        $node->appendChild($nodeText);

        return true;
    }

    /**
     * @param  $node
     * @param  $val
     * @return bool
     */
    protected function appendCData($node, $val)
    {
        $nodeText = $this->dom->createCDATASection($val);
        $node->appendChild($nodeText);

        return true;
    }

    /**
     * @param  $node
     * @param  $fragment
     * @return bool
     */
    protected function appendDocumentFragment($node, $fragment)
    {
        if ($fragment instanceof DOMDocumentFragment) {
            $node->appendChild($fragment);
            return true;
        }

        return false;
    }

}