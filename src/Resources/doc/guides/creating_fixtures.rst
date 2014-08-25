Creating a simple fixture class
===============================

Let's walk through it with the same example as in the DoctrineFixtureBundle documentation.
Imagine that you have a ``User`` class, and you would like to load a couple of ``User`` entries.

First, we would create a YAML file with the necessary information:

.. code-block:: yaml

    # src/Acme/HelloBundle/DataFixtures/ORM/Data/User.yml
    
    options:
      add_reference: true
    data:
      admin:
        username: admin
        password: admin
      test:
        username: test
        password: test

*Note: we will explain what* ``options`` *are available and what they do later on.*

Then, we will need a fixture class to load the information:

.. code-block:: php

    <?php
    // src/Acme/HelloBundle/DataFixtures/ORM/LoadUserData.php
    
    namespace Acme\HelloBundle\DataFixtures\ORM;
    
    class LoadUserData extends AcmeHelloOrmFixture
    {
        protected $name = 'User';
    }

That's it! Well, not so fast. Notice ``LoadUserData`` is extending ``AcmeHelloOrmFixture``,
which we have not written yet. Luckily, it is also quite simple and we only need one like
this per bundle. Here it is:

.. code-block:: php

    <?php
    // src/Acme/HelloBundle/DataFixtures/ORM/AcmeHelloOrmFixture.php

    namespace Acme\HelloBundle\DataFixtures\ORM;

    use Zanui\FixturesBundle\DataFixtures\ZanuiOrmFixture;
    
    abstract class AcmeHelloOrmFixture extends ZanuiOrmFixture
    {
        protected $baseDir = __DIR__;
    }

And now that really is it!

Of course, you could choose to add the ``baseDir`` property on the
loading classes and extend them directly from ``ZanuiOrmFixture``, but if you have a lot of
classes this is the preferred way to go. Anyway, this is how the ``LoadUserData`` would look
like in that case:

.. code-block:: php

    <?php
    // src/Acme/HelloBundle/DataFixtures/ORM/LoadUserData.php

    namespace Acme\HelloBundle\DataFixtures\ORM;

    use Zanui\FixturesBundle\DataFixtures\ZanuiOrmFixture;

    class LoadUserData extends ZanuiOrmFixture
    {
        protected $baseDir = __DIR__;
        protected $name = 'User';
    }

Notice that without ``AcmeHelloOrmFixture``, we would need to add the ``use`` statement and
the ``baseDir`` property to all fixture classes.

You might feel like there is still something missing: how is ``ZanuiOrmFixture`` calling
the setter methods for my ``User`` entity, or even creating the entity in the first place?
You are right to feel that way, but everything works because we followed a specific
directory structure and stuck to a few naming conventions.
