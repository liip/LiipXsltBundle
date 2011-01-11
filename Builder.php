<?php

namespace Bundle\Liip\XsltBundle;

class Builder
{
    protected $dom;

    public function __construct($value) {

        $this->dom = new \DOMDocument();
        $root = $this->dom->createElement('page');
        $this->dom->appendChild($root);

        $this->parse($root, $value);
    }
  
    protected function parse($parentNode, $arr)
    {
      if (!is_array($arr)) { return false; }

      $append = true;
      foreach ( $arr as $key => $data )
      {
        if(is_array($data) && is_numeric($key)!==true){
          if(array_values($data) === $data)
          {
            foreach($data as $numeric_key => $subdata)
            {
              $this->appendNode ( $parentNode, $subdata, $key );
            }
          }
          else
          {
            $this->appendNode ( $parentNode, $data, $key );
          }
        }
        elseif(is_numeric($key))
        {
          $append = $this->appendNode ( $parentNode, $data, "object" );
        }
        elseif (self::isElementNameValid ( $key )){
          $append = $this->appendNode ( $parentNode, $data, $key );
        }
      }

      return $append;
    }

    protected function appendNode($parentNode, $data, $nodename)
    {
      $node = $this->dom->createElement ( $nodename );
      $appendNode = $this->selectNodeType ( $node, $data );
      if ($appendNode)
      {
        $parentNode->appendChild ( $node );
      }
      return true;
    }
  
    public static function isElementNameValid($strName)
    {
      // Element name validation
      if (strpos ( $strName, ' ' ) !== False || $strName == '')
      {
        // Bad key name, skip this entry
        return false;
      }
      //If key is not alpha numeric
      if (! preg_match ( '|^\w+$|', $strName ))
      {
        // Bad key name, skip this entry
        return false;
      }

      return true;
    }

    protected function selectNodeType($node, $val)
    {
      $append = true;
      if (is_array ( $val ))
      {
        $append = $this->appendInlineArray ( $node, $val );
      }
      elseif (is_numeric ( $val ))
      {
        $append = $this->appendNumeric ( $node, $val );
      }
      elseif (is_string ( $val ))
      {
        if (isValidDateTime ( $val ))
        {
          $append = $this->appendDateTime ( $node, $val );
        }
        else
        {
          $append = $this->appendCData ( $node, $val );
        }
      }
      elseif (is_bool ( $val ))
      {
        $append = $this->appendCData ( $node, intval ( $val ) );
      }
      elseif ($val instanceof \DOMNode) {
        $child = $this->dom->importNode($val, true);
        $node->appendChild($child);
      }
      elseif ($val instanceof \SimpleXMLElement) {
        $node = dom_import_simplexml($val);
        $child = $this->dom->importNode($node, true);
        $node->appendChild($child);
      }
      return $append;
    }

    protected function appendXMLString($node, $val)
    {
      if (strlen ( $val ) > 0)
      {
        $frag = $this->dom->createDocumentFragment ();
        $frag->appendXML ( $val );
        $node->appendChild ( $frag );
        return true;
      }
      return false;
    }

    protected function appendNumeric($node, $val)
    {
      $nodeText = $this->dom->createTextNode ( $val );
      $node->appendChild ( $nodeText );
      return true;
    }

    protected function appendDateTime($node, $val)
    {
      $ts = strtotime ( $val );
      $nodeText = $this->dom->createCDATASection ( $val );
      $node->setAttribute ( "ts", $ts );
      $node->setAttribute ( "d", date ( "d", $ts ) ); // 2 digit date with leading zero
      $node->setAttribute ( "j", date ( "j", $ts ) ); // 2 digit date without leading zero
      $node->setAttribute ( "D", date ( "D", $ts ) );
      $node->setAttribute ( "l", date ( "l", $ts ) );
      $node->setAttribute ( "S", date ( "S", $ts ) );
      $node->setAttribute ( "m", date ( "m", $ts ) );
      $node->setAttribute ( "M", date ( "M", $ts ) );
      $node->setAttribute ( "F", date ( "F", $ts ) );
      $node->setAttribute ( "n", date ( "n", $ts ) );
      $node->setAttribute ( "t", date ( "t", $ts ) );
      $node->setAttribute ( "y", date ( "y", $ts ) );
      $node->setAttribute ( "Y", date ( "Y", $ts ) );
      $node->setAttribute ( "g", date ( "g", $ts ) );
      $node->setAttribute ( "G", date ( "G", $ts ) );
      $node->setAttribute ( "h", date ( "h", $ts ) );
      $node->setAttribute ( "H", date ( "H", $ts ) );
      $node->setAttribute ( "i", date ( "i", $ts ) );
      $node->setAttribute ( "s", date ( "s", $ts ) );
      $node->appendChild ( $nodeText );
      return true;
    }

    protected function appendCData($node, $val)
    {
      $nodeText = $this->dom->createCDATASection ( $val );
      $node->appendChild ( $nodeText );
      return true;
    }

    protected function appendDocumentFragment($node, $fragment)
    {
      if ($fragment instanceof DOMDocumentFragment)
      {
        $node->appendChild ( $fragment );
        return true;
      }
      return false;
    }

    public function getDOM() {
        return $this->dom;
    }
}

function isValidDateTime($dateTime)
{
  $matches = array ();
  if (preg_match ( "/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $dateTime, $matches ))
  {
    if (isset ( $matches [1] ) && isset ( $matches [2] ) && isset ( $matches [3] ))
    {
      if (checkdate ( $matches [2], $matches [3], $matches [1] ))
      {
        return true;
      }
    }
  }

  return false;
}