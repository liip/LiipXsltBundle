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
                        "link" => "http://foo.com",
                        "test" => array(
                            0 => array(
                                "title" => "Test Title 1",
                                "link" => "http://foo.com"
                            )
                        ),
                    ),
                    1 => array(
                        "title" => "Test Title 2",
                        "link" => "http://bar.com"
                    )
                )
            )
        );

        $content = array(
            "Result" => array(
                132 => array(
                    "id" => 132,
                    "title" => "Content 132"
                ),
                162 => array(
                    "id" => 162,
                    "title" => "Content 162"
                ),
                1004 => array(
                    "id" => 1004,
                    "title" => "Content 1004"
                ),
                1321 => array(
                    "id" => 1321,
                    "title" => "Content 1321"
                ),
                1620 => array(
                    "id" => 1620,
                    "title" => "Content 1620"
                ),
                104 => array(
                    "id" => 104,
                    "title" => "Content 104"
                )
            )
        );
        $parameters = array("rss" => $rss, "SearchResults" =>$content);

        $builder = new Builder($parameters);
        $xpath = new \DOMXpath($builder->getDOM());

        //Test that the channel path exists

        $elements = $xpath->query("/page/rss/channel");
        $this->assertEquals(1, $elements->length);

        $elements = $xpath->query("/page/rss/channel/item");
        $this->assertEquals(2, $elements->length);

        $elements = $xpath->query("/page/SearchResults/Result");
        $this->assertEquals(6, $elements->length);
    }

}