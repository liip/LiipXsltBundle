<?php

namespace Bundle\Liip\XsltBundle;

interface Extension {

    public function apply(\DOMDocument $dom);
}
