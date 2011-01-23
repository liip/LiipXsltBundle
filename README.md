XsltBundle
==========

Renderer for XSLT templates in Symfony2.

Installation
============

1. Add this bundle to your project as Git submodule:

        $ git submodule add git://github.com/liip/XsltBundle.git src/Bundle/Liip/XsltBundle
        $ git submodule update --recursive --init

2. Add the bundle to your application kernel:

        // app/AppKernel.php
        public function registerBundles()
        {
            return array(
                // ...
                new Liip\XsltBundle\LiipXsltBundle(),
                // ...
            );
        }

3. Add the bundle to your application config:

        # app/config/config.yml
        xslt.config: -

        # app/config/config.xml
        <xslt:config />

Usage
=====

Create an XSLT file in your views folder. Then in your controller simply call:

    return $this->render('HelloBundle:Hello:index.html.xsl', array('name' => $name));

Extensions
==========

Extension can be used to add global data to the XML or to register PHP function callbacks.

This bundle already includes different extensions. To use them, use this in your application config:

        # app/config/config.yml
        xslt.config:
             extensions: [xslt.extension.environment, xslt.extension.routes, xslt.extension.debug]

