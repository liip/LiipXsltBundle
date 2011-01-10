XsltBundle
==========

Renderer for XSLT templates in Symfony2.

Installation
============

1. Add the DoctrinePHPCRBundle and the jackalope library to your project as git submodules:

    $ git submodule add git://github.com/liip/XsltBundle.git src/Bundle/XsltBundle
    $ git submodule update --recursive --init

2. Add the bundle to your application kernel:

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Bundle\XsltBundle\XsltBundle(),
            // ...
        );
    }

3. Add the bundle to your application config:

    # app/config/config.yml
    xslt.config:
        debug: false

    # app/config/config.xml
    <xslt:config>
        <debug>false</debug>
    </xslt:config>
