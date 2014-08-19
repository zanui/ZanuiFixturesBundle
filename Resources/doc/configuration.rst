Configuration
=============

The **ZanuiFixturesBundle** can be used without any configuration, but there are two convenience fallback
parameters that you can define in your ``config.yaml`` to facilitate the creation of fixtures:

``entity_namespace_fallback`` (string)
    Defines a namespace to load entities from when a ``namespace`` property is not explicitly declared
    in the fixture class. If all (or most) of your entities belong to a common namespace, adding that
    namespace here will save you from having to add it in every fixture class.

``base_order_fallback`` (integer, defaults to ``1``)
    Defines a base order for loading fixtures when an ``order`` property is not explicitly declared
    in the fixture class.

Here is a typical configuration:

.. code-block:: yaml

    # app/config/config.yml

    zanui_fixtures:
      entity_namespace_fallback: 'Acme\HelloBundle\Entity'
      base_order_fallback: 100
