Controlling the loading order
=============================

When we want to create a new fixture that depends on other fixtures, we will need to make
sure that it is loaded after all its dependencies. To do that, we simply need to add an ``order``
property to the class and make its value higher than that of all its dependencies.

We will also need to link the entity to its dependencies (foreign keys) in the YAML file.
We do that by setting the value of the foreign key to be the key of the entity it depends on.
Take the following example, in which we add a ``Group`` entity...:

.. code-block:: yaml

    # src/Acme/HelloBundle/DataFixtures/ORM/Data/Group.yml
    
    options:
      add_reference: true
    data:
      admin:
        group_name: admin

.. code-block:: php

    <?php
    // src/Acme/HelloBundle/DataFixtures/ORM/LoadGroup.php
    
    namespace Acme\HelloBundle\DataFixtures\ORM;
    
    class LoadGroup extends AcmeHelloOrmFixture
    {
        protected $name = 'Group';
    }

...  and a ``UserGroup`` entity to assign a ``User`` to a ``Group``:

.. code-block:: yaml

    # src/Acme/HelloBundle/DataFixtures/ORM/Data/userGroup.yml
    
    options:
      foreign_keys:
        - user
        - group
    data:
      -
        user: User-admin
        group: Group-admin

.. code-block:: php

    <?php
    // src/Acme/HelloBundle/DataFixtures/ORM/LoadUserGroup.php
    
    namespace Acme\HelloBundle\DataFixtures\ORM;
    
    class LoadUserGroup extends AcmeHelloOrmFixture
    {
        protected $name = 'UserGroup';
        protected $order = 200;
    }

Notice that we referred to the admin user by making ``user`` have the value ``User-admin``,
in which the first part is the entity it refers to and the second part was the key for the
admin user as defined in its YAML file (same applies for ``group``). Also notice that the order is
set to 200 to make sure ``User`` and ``Group`` are already loaded when we process ``UserGroup``.

The loader knows that those values are foreign keys because we explicitly listed them using the
``foreign_keys`` option. Any fields that start with ``fk_`` (case insensitive)
are automatically inferred to be foreign keys, so they don't need to be included in the list.
The references exist because we added the option ``add_reference: true`` to our ``User.yml`` and
``Group.yml`` files.

We have just gone through examples that required the use of options, so let's jump straight into
that topic and describe all available options.
