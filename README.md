XsltBundle
==========

Renderer for XSLT templates in Symfony2.

Installation
============

1.  Add the following lines in your deps file:
    
    ```
    [LiipXsltBundle]
        git=http://github.com/liip/LiipXsltBundle.git
        target=/bundles/Liip/XsltBundle
    ```

2.  Run the vendors script:
    
    ```
    $ php bin/vendors install
    ```

3.  Add the Liip namespace to your autoloader:
    
    ```
    // app/autoload.php
    $loader->registerNamespaces(array(
        'Liip' => __DIR__.'/../vendor/bundles',
        // your other namespaces
    ));
    ```

4.  Add the bundle to your application kernel:
    
    ```
    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Liip\XsltBundle\LiipXsltBundle(),
            // ...
        );
    }
    ```

5.  Enable the XSLT template engine in the config
    
    ```
    # app/config/config.yml
    # ...
    templating:      { engines: ['twig', 'xslt'] }
    # ...
    ```


Usage
=====

Create an XSLT file in your views folder. Then in your controller simply call,
where the name of the Bundle is ``HelloBundle`` and the name of the controller
is ``HelloController``:

    return $this->render('HelloBundle:Hello:index.html.xsl', array('name' => $name));

Extensions
==========

Extension can be used to add global data to the XML or to register PHP function callbacks.

This bundle already includes different extensions. To use them, use this in your application config:

    # app/config/config.yml
    liip_xslt:
         extensions: [liip_xslt.extension.environment, liip_xslt.extension.routes, liip_xslt.extension.debug]

