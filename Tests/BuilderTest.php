<?php

namespace Bundle\Liip\XsltBundle\Tests;

use Bundle\Liip\XsltBundle\Builder;
use Bundle\Liip\XsltBundle\Tests\TestCase;

class BuilderTest extends TestCase
{
    
    public function testArrayToDomDocumentConversion()
    {
        $rss = array(
           "channel" => array(
             "title" => "Test RSS",
             "description" => "Test desc",
             "item" => array(
               0 => array(
                 "title" => "Test Title 1",
                 "link" => "http://foo.com"
               ),
               1 => array(
                 "title" => "Test Title 2",
                 "link" => "http://bar.com"
               )
             )
           )
        );
        $parameters = array("rss"=>$rss);

        $builder = new Builder($parameters);
        $xpath = new \DOMXpath($builder->getDoc());

        //Test that the channel path exists

        $elements = $xpath->query("/page/rss/channel");
        $this->assertEquals(1, count($elements));
    }

}