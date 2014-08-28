Available data options and fixture properties
=============================================

Data options
------------

``flush_preserving_ids`` (boolean, defaults to ``false``)
    Indicates whether the entities should be saved overriding the default `ID generation strategy <http://doctrine-orm.readthedocs.org/en/latest/reference/basic-mapping.html#identifier-generation-strategies>`_ to preserve the given IDs. This is only necessary if in some parts of your applications you have assumed that some entities have a certain ID (*eg.* to simplify queries).

``flush_on_every_row`` (boolean, defaults to ``false``)
    Indicates whether the entity should be flushed on every row instead of only at the end, *eg.* you depend on the ID of a previous row, like in a parent-child relationship.

``add_reference`` (boolean, defaults to ``false``)
    Indicates whether to set a reference for the current entity. Only necessary if the entity will act as a foreign key for other entities.

``foreign_keys`` (array)
    Defines a list of fields that should be treated as foreign keys, *ie.* their values point to a previously saved reference. Fields that start with ``fk_`` (case insensitive) are assumed to be foreign keys, so they do not need to be listed.

``date_time_fields`` (array)
    Defines a list of fields which values should be transformed to ``DateTime``, *eg.* a value of ``2000-01-01`` would be passed to the setter as ``\DateTime('2000-01-01')``.

``local_references`` (array, only for fixtures using a``ZanuiCustomLoader``)
    Similar to ``foreign_keys``, but in this case the references point to entities saved within the same loader. They are especially useful when writing custom loaders.

Fixture properties
------------------

We have mentioned all of the following properties in previous sections, but here is a definition for relevant
properties for fixture classes extending the ``ZanuiOrmFixture`` or ``ZanuiCustomLoader`` class:

``baseDir``
    Defines the base directory where data will be loaded from. Typically it will simply be ``__DIR__``.
    As shown above, it is usually a good idea to set in your own base class and extend the rest of the
    fixture classes from it.

``name``
    Defines the name of the fixture. In the case of fixtures extending ``ZanuiOrmFixture``,
    it must match the name of the YAML file where the data is stored to work out of the box. In
    the case of ``ZanuiCustomLoader``, it must match the directory name in which the YAML files are stored.

``namespace`` (only relevant for ``ZanuiOrmFixture``)
    Defines the namespace to use in order to load the entity being loaded.
    It falls back to the ``entity_namespace_fallback`` parameter described above.

``order``
    Defines the order in which the fixture should be loaded. Fixtures with higher ``order`` will be loaded after
    fixtures with lower ``order``.
    It falls back to the ``base_order_fallback`` parameter described above.
