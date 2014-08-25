Installation
============

To install, add the following to your ``composer.json`` file:

.. code-block:: json

    {
        "require": {
            "zanui/zanui-fixtures-bundle": "2.0.*"
        }
    }

Update the vendor libraries:

.. code-block:: bash

    $ php composer.phar update zanui/zanui-fixtures-bundle

Finally, register the ``DoctrineFixturesBundle`` and the
``ZanuiFixturesBundle`` in ``app/AppKernel.php``.

.. code-block:: php

    // ...
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new Zanui\FixturesBundle\ZanuiFixturesBundle(),
            // ...
        );
        // ...
    }
