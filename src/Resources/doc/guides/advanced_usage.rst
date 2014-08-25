Advanced usage
==============

Following the directory structure and naming conventions is recommended but not required.
You may decide to extend any of the classes included in this bundle to change the default behaviour.

For example, you may want to override the ``load(...)`` and ``loadInfo(...)`` methods of the ``ZanuiOrmFixture``
class to follow your own conventions. You may even use the conventions in this bundle for some fixtures and
extend directly from ``AbstractFixture`` of the Doctrine2 Data Fixtures library for others.
