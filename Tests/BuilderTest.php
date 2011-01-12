<?php

namespace Bundle\Liip\XsltBundle;

class BuilderTest extends \PHPUnit_Framework_TestCase
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
        $parameters = array("rss" => $rss);

        $builder = new Builder($parameters);
        $xpath = new \DOMXpath($builder->getDOM());

        //Test that the channel path exists

        $elements = $xpath->query("/page/rss/channel");
        $this->assertEquals(1, $elements->length);

        $elements = $xpath->query("/page/rss/channel/item");
        $this->assertEquals(2, $elements->length);
    }

}