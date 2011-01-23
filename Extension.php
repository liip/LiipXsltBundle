<?php

namespace Liip\XsltBundle;

interface Extension {

    public function apply(\DOMDocument $dom);
}
