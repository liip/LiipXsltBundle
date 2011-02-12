<?php

namespace Liip\XsltBundle;

use Symfony\Component\Serializer\Encoder\XmlEncoder as BaseXmlEncoder;

class XmlEncoder extends BaseXmlEncoder
{
    const FORMAT_XML = 'xml';

    public function encode($data, $format = self::FORMAT_XML)
    {
        return parent::encode($data, $format);
    }

    public function getDom()
    {
        return $this->dom;
    }
}
