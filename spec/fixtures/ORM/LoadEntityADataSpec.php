<?php

namespace spec\fixtures\ORM;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use fixtures\Entity\EntityA;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zanui\FixturesBundle\Exception\InvalidOptionException;
use Zanui\FixturesBundle\Exception\LoadInfoException;

class LoadEntityADataSpec extends ObjectBehavior
{
    protected $expectedData = array(
        'first_entry' => array(
            'field_a' => 'value_a',
            'field_b' => 'value_b'
        )
    );

    function it_is_initializable()
    {
        $this->shouldHaveType('fixtures\ORM\LoadEntityAData');
    }

    function it_is_a_zanui_orm_fixture()
    {
        $this->shouldHaveType('Zanui\FixturesBundle\DataFixtures\ZanuiFixture');
        $this->shouldHaveType('Zanui\FixturesBundle\DataFixtures\ZanuiOrmFixture');
        $this->shouldHaveType('Doctrine\Common\DataFixtures\FixtureInterface');
        $this->shouldHaveType('Doctrine\Common\DataFixtures\OrderedFixtureInterface');
        $this->shouldHaveType('Symfony\Component\DependencyInjection\ContainerAwareInterface');
    }

    function it_sets_fallback_parameters_from_the_container(
        ContainerInterface $container
    ) {
        $container->hasParameter('entity_namespace_fallback')->willReturn(true);
        $container->getParameter('entity_namespace_fallback')->willReturn('some\namespace');

        $container->hasParameter('base_order_fallback')->willReturn(true);
        $container->getParameter('base_order_fallback')->willReturn(1);

        $this->setNamespace(null);
        $this->setContainer($container);

        $this->getNamespace()->shouldReturn('some\namespace');
        $this->getOrder()->shouldReturn(1);
    }

    function it_recognises_valid_options()
    {
        $this->isValidDataOption('add_reference')->shouldReturn(true);
        $this->isValidDataOption('some_invalid_option')->shouldReturn(false);
    }

    function it_is_able_to_set_simple_fields_for_a_given_entity() {
        $entityA = new EntityA();
        $this->setField($entityA, 'field_a', 'value');
        $this->getEntity()->getFieldA()->shouldReturn('value');
    }

    function it_is_able_to_set_a_foreign_key_for_a_given_entity(
        ReferenceRepository $referenceRepository
    ) {
        $entityA = new EntityA();
        $options = array('foreign_keys' => array('field_a'));

        $referenceRepository->getReference('some_reference')->willReturn('value');
        $this->setReferenceRepository($referenceRepository);

        $this->setField($entityA, 'field_a', 'some_reference', $options);
        $this->getEntity()->getFieldA()->shouldReturn('value');
    }

    function it_does_not_allow_to_check_if_invalid_options_are_enabled()
    {
        $this
            ->shouldThrow(new InvalidOptionException('"option_a" is not a valid option.'))
            ->during('isOptionEnabled', array('option_a', array()));
    }

    function it_can_tell_if_valid_options_are_enabled()
    {
        $optionEnabled = array('add_reference' => true);
        $optionDisabled = array('add_reference' => false);

        $this
            ->isOptionEnabled('add_reference', $optionEnabled)
            ->shouldBe(true);

        $this
            ->isOptionEnabled('add_reference', array())
            ->shouldBe(false);

        $this
            ->isOptionEnabled('add_reference', $optionDisabled)
            ->shouldBe(false);

        $this
            ->isOptionDisabled('add_reference', $optionEnabled)
            ->shouldBe(false);

        $this
            ->isOptionDisabled('add_reference', array())
            ->shouldBe(true);

        $this
            ->isOptionDisabled('add_reference', $optionDisabled)
            ->shouldBe(true);
    }

    function it_recognises_foreign_keys()
    {
        $options = array('foreign_keys' => array('field_a'));

        $this->isForeignKey('field_a', $options)->shouldReturn(true);
        $this->isForeignKey('field_b', $options)->shouldReturn(false);

        $this->isForeignKey('fk_field', $options)->shouldReturn(true);
    }

    function it_recognises_local_references()
    {
        $options = array('local_references' => array('field_a'));

        $this->isLocalReference('field_a', $options)->shouldReturn(true);
        $this->isLocalReference('field_b', $options)->shouldReturn(false);
    }

    function it_can_retrieve_data_options()
    {
        $options = array('option_a' => '1', 'option_b' => 2);
        $info = array('options' => $options);

        $this->getOptions($info)->shouldReturn($options);
    }

    function it_can_flush_data(ObjectManager $manager)
    {
        $manager->flush()->shouldBeCalled();

        $this->flush($manager, 'fixtures\ORM\LoadEntityAData', array());
    }

    function it_can_flush_data_preserving_ids(
        ObjectManager $manager,
        ClassMetadata $metadata
    ) {
        $manager->flush()->shouldBeCalled();
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE)->shouldBeCalled();
        $metadata->setIdGeneratorType('typeA')->shouldBeCalled();

        $metadata->generatorType = 'typeA';

        $manager
            ->getClassMetadata('fixtures\ORM\LoadEntityAData')
            ->willReturn($metadata);

        $options = array('flush_preserving_ids' => true);
        $this->flush($manager, 'fixtures\ORM\LoadEntityAData', $options);
    }

    function it_infers_a_reference_key()
    {
        $this
            ->getReferenceKey('entity_key', 'item_key', '')
            ->shouldReturn('entity_key-item_key');

        $this
            ->getReferenceKey('entity_key', 'item_key', '-123')
            ->shouldReturn('entity_key-item_key-123');
    }

    function it_generates_unique_suffixes()
    {
        $this->generateUniqueSuffix()
            ->shouldNotBeEqualTo($this->generateUniqueSuffix());
    }

    function it_loads_info_from_the_data_folder(
        ObjectManager $manager
    ) {
        $manager->persist(
            Argument::type('fixtures\Entity\EntityA')
        )->shouldBeCalled();

        $manager->flush()->shouldBeCalled();

        $expectedInfo = array('data' => $this->expectedData);

        $this->load($manager);
        $this->getInfo()->shouldReturn($expectedInfo);
    }

    function it_optionally_adds_references(
        ObjectManager $manager,
        ReferenceRepository $referenceRepository
    ) {
        $dataFileContent = array(
            'options' => array('add_reference' => true),
            'data' => $this->expectedData
        );

        $referenceRepository->addReference(
            'EntityA-first_entry',
            Argument::type('fixtures\Entity\EntityA')
        )->shouldBeCalled();

        $this->setReferenceRepository($referenceRepository);
        $this->setDataFileContent($dataFileContent);
        $this->load($manager);
    }

    function it_does_not_load_data_without_a_data_key(
        ObjectManager $manager
    ) {
        $dataFileContent = array('no_data_key' => $this->expectedData);

        $this->setDataFilename('file');
        $this->setDataFileContent($dataFileContent);

        $this
            ->shouldThrow(new LoadInfoException('File file does not have a data key.'))
            ->during('load', array($manager));
    }

    function it_enforces_data_to_be_array(
        ObjectManager $manager
    ) {
        $this->setDataFilename('file');
        $this->setDataFileContent('not_an_array');

        $this
            ->shouldThrow(new LoadInfoException('File file could not be parsed into an array.'))
            ->during('load', array($manager));
    }
}
