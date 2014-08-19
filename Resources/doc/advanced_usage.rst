Advanced usage
==============

Following the directory structure and naming conventions is recommended but not required.
You may decide to extend any of the classes included in this bundle to change the default behaviour.

For example, you may want to override the ``load(...)`` and ``loadInfo(...)`` methods of the ``ZanuiOrmFixture``
class to follow your own conventions. You may even use the conventions in this bundle for some fixtures and
extend directly from ``AbstractFixture`` of the Doctrine2 Data Fixtures library for others.

Writing custom loaders
----------------------

This bundle also provides a ``ZanuiCustomLoader`` class to help create classes that load data into several
(usually related) entities. Imagine we want to load data about a ``team`` and its ``members``. With a custom
loader, we can define a YAML file per team and define all relevant data within that same file, instead of
having teams and members split into several YAML files. This is how the YAML file would look like:

.. code-block:: yaml

    # src/Acme/HelloBundle/DataFixtures/Teams/a-team.yml

    data:
      team:
        options:
          add_reference: true
        data:
          -
            name: A-team
            motto: If you can find them... maybe you can hire... The A-Team.

      member:
        options:
          local_references:
            - team
        data:
          -
            team: team-0
            name: Hannibal
          -
            team: team-0
            name: Murdock

And here is your custom loader, which extends ``ZanuiCustomLoader``:

.. code-block:: php

    <?php
    // src/Acme/HelloBundle/DataFixtures/TeamLoader.php

    namespace Acme\HelloBundle\DataFixtures;

    use Doctrine\Common\Persistence\ObjectManager;
    use Zanui\FixturesBundle\DataFixtures\ZanuiCustomLoader;
    use Acme\HelloBundle\Entity\Team;
    use Acme\HelloBundle\Entity\Member;

    class TeamLoader extends ZanuiCustomLoader
    {
        protected $name = 'Teams';
        protected $order = 1000;
        protected $baseDir = __DIR__;

        public function load(ObjectManager $manager)
        {
            $this->manager = $manager;
            $this->info = $this->loadInfo();

            foreach ($this->info as $current) {
                $this->current = $current;
                $this->referenceUniqueSuffix = $this->getUniqueSuffix();

                $this->loadCustomEntity(new Team(), 'team');
                $this->loadCustomEntity(new Member(), 'member');
            }

            $manager->flush();
        }
    }

The custom loader will load all files under ``src/Acme/HelloBundle/DataFixtures/Teams/`` (the ``name``
property of the loader needs to match that of the directory), so next to ``a-team.yml`` you could
add other team files and they would be processed automatically.

References in custom loaders are saved with a unique ID to avoid collisions, so they cannot be used outside
the custom loader. Local references have to be explicitly declared using the ``local_references`` option.
Notice how for each team member we refer to their team as ``team-0``, as their team is the first one defined in
the file. Although several teams could be defined within the same file, it is recommended to divide them into
separate files.
