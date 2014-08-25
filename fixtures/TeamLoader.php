<?php

namespace fixtures;

use Doctrine\Common\Persistence\ObjectManager;
use Zanui\FixturesBundle\DataFixtures\ZanuiCustomLoader;

class TeamLoader extends ZanuiCustomLoader
{
    protected $name = 'Teams';
    protected $order = 1000;
    protected $baseDir = __DIR__;

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->info = $this->loadInfo();

        $teamClass = 'fixtures\Entity\Team';
        $memberClass = 'fixtures\Entity\Member';

        foreach ($this->info as $current) {
            $this->current = $current;
            $this->referenceUniqueSuffix = $this->generateUniqueSuffix();

            $this->loadCustomEntity($teamClass, 'team');
            $this->loadCustomEntity($memberClass, 'member');
        }

        $manager->flush();
    }
}
