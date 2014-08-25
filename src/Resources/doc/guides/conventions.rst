Directory structure and naming conventions
==========================================

The ``AcmeHelloOrmFixture`` sets the ``baseDir`` to ``__DIR__``, which will make ``ZanuiOrmFixture``
look for YAML files inside ``__DIR__/Data/`` with the filename matching that of the fixture.
The name of the class ``LoadUserData`` can be anything, as long as its ``name`` property matches an
existing file inside the ``Data`` directory. The ``name`` also needs to match that of the entity class.

.. code-block:: text

    Acme/
    `-- HelloBundle/
        `-- DataFixtures/
            `-- ORM/
                |-- Data/
                |   |-- User.yml
                |   `-- (other data files)
                |-- AcmeHelloOrmFixture.php
                |-- LoadUserData.php
                `-- (other fixture classes)

The names of the entity fields inside the YAML file also need to follow a convention,
as the the bundle uses it to infer the setter method to call in order to set their value:

- To have a setter method called ``setUsername`` invoked, the field in the YAML file needs to be called ``username`` or ``Username``
- If the setter method was called ``setUserName``, then the field would need to be called ``user_name`` or ``UserName``.

You get the idea.

The ``ZanuiFixture`` class has a property ``namespace`` that falls back to the ``entity_namespace_fallback``
parameter. If the ``User`` entity class did not belong to that namespace, or ``entity_namespace_fallback``
was not declared in the bundle's configuration, we would need to add the correct namespace for the
``LoadUserData`` class:

.. code-block:: php

    <?php
    // src/Acme/HelloBundle/DataFixtures/ORM/LoadUserData.php
    
    namespace Acme\HelloBundle\DataFixtures\ORM;
    
    class LoadUserData extends AcmeHelloOrmFixture
    {
        protected $namespace = 'Acme\OtherBundle\Entity';
        protected $name = 'User';
    }
