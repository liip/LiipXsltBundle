<?php

namespace Liip\XsltBundle\Extension;

interface Extension {

    public function apply(\DOMDocument $dom);
}
