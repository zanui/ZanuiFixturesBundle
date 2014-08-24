<?php

namespace spec\fixtures;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use fixtures\Entity\Team;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TeamLoaderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('fixtures\TeamLoader');
    }

    function it_is_a_zanui_custom_loader()
    {

        $this->shouldHaveType('Zanui\FixturesBundle\DataFixtures\ZanuiFixture');
        $this->shouldHaveType('Zanui\FixturesBundle\DataFixtures\ZanuiCustomLoader');
        $this->shouldHaveType('Doctrine\Common\DataFixtures\FixtureInterface');
        $this->shouldHaveType('Doctrine\Common\DataFixtures\OrderedFixtureInterface');
        $this->shouldHaveType('Symfony\Component\DependencyInjection\ContainerAwareInterface');
    }

    function it_can_load_data_for_several_entities(
        ObjectManager $manager,
        ReferenceRepository $referenceRepository
    ) {
        $referenceRepository->addReference(
            Argument::containingString('team-0'),
            Argument::type('fixtures\Entity\Team')
        )->shouldBeCalled();

        $manager
            ->persist(Argument::type('fixtures\Entity\Team'))
            ->shouldBeCalled();

        $manager
            ->persist(Argument::type('fixtures\Entity\Member'))
            ->shouldBeCalled();

        $manager->flush()->shouldBeCalled();

        $this->setReferenceRepository($referenceRepository);
        $this->load($manager);

        $this->getInfo()->shouldHaveCount(1);
    }
}
