<?php
/**
 * Zanui (http://www.zanui.com.au/)
 *
 * @link      http://github.com/zanui/shop for the canonical source repository
 * @copyright Copyright (c) 2011-2014 Internet Services Australia 3 Pty Limited (http://www.zanui.com.au)
 * @license   The MIT License (MIT)
 */

namespace Zanui\FixturesBundle\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Zanui\FixturesBundle\Exception\InvalidOptionException;

/**
 * Zanui fixture
 *
 * This is the base class for all our fixtures,
 * and makes them container aware and ordered.
 */
abstract class ZanuiFixture extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * Indicates whether the entities should be saved overriding
     * the default generation type to preserve the given IDs
     */
    const DATA_OPTION_FLUSH_PRESERVING_IDS = 'flush_preserving_ids';

    /**
     * Whether to set a reference for the current entity
     */
    const DATA_OPTION_ADD_REFERENCE = 'add_reference';

    /**
     * List of fields that should be considered foreign keys.
     * All fields starting with fk_ are considered foreign keys by default,
     * so they don't need to be included in the list.
     */
    const DATA_OPTION_FOREIGN_KEYS = 'foreign_keys';

    /**
     * List of fields that point to local references.
     * This is necessary to simplify custom loaders.
     */
    const DATA_OPTION_LOCAL_REFERENCES = 'local_references';

    /**
     * Whether the entity should be flushed on every row instead of only at the end.
     * Necessary for CatalogCategory due to the nested set behaviour.
     */
    const DATA_OPTION_FLUSH_ON_EVERY_ROW = 'flush_on_every_row';

    /**
     * Defines the separator to use for the local reference suffix
     */
    const LOCAL_REFERENCE_SEPARATOR = '-';

    /**
     * @var string The namespace to use when creating entities
     */
    protected $namespace;

    /**
     * @var string The name of the fixture
     */
    protected $name;

    /**
     * @var int The order in which the fixture should be loaded
     */
    protected $order;

    /**
     * @var string The base directory for the fixture. The data will be loaded from the Data subdirectory.
     */
    protected $baseDir;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array Available data options
     */
    protected $dataOptions = array(
        self::DATA_OPTION_FLUSH_PRESERVING_IDS,
        self::DATA_OPTION_ADD_REFERENCE,
        self::DATA_OPTION_FOREIGN_KEYS,
        self::DATA_OPTION_LOCAL_REFERENCES,
        self::DATA_OPTION_FLUSH_ON_EVERY_ROW
    );

    /**
     * Loads info from a YAML file matching the given name or,
     * if no name is given, the name of the fixture.
     *
     * @param string $dir  Path to the directory from where to get the data
     * @param string $name Filename from where to grab the data
     *
     * @return array
     */
    abstract protected function loadInfo($dir = null, $name = null);

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        if (!isset($this->namespace) && $this->container->hasParameter('entity_namespace_fallback')) {
            $this->namespace = $this->container->getParameter('entity_namespace_fallback');
        }

        if (!isset($this->order) && $this->container->hasParameter('base_order_fallback')) {
            $this->order = $this->container->getParameter('base_order_fallback');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Sets a field of a given entity
     *
     * @param mixed  $entity
     * @param string $fieldName
     * @param string $fieldValue
     * @param array  $options
     */
    protected function setField($entity, $fieldName, $fieldValue, array $options = array())
    {
        $setterName = $this->fieldToSetterMethod($fieldName);

        if ($this->isForeignKey($fieldName, $options)) {
            $entity->$setterName($this->getReference($fieldValue));
        } else {
            $entity->$setterName($fieldValue);
        }
    }

    /**
     * Asserts whether the given option is valid or not
     *
     * @param string $optionName
     *
     * @return bool
     */
    protected function isValidDataOption($optionName)
    {
        return in_array($optionName, $this->dataOptions);
    }

    /**
     * Returns true if an option is enabled and false otherwise
     *
     * @param string $optionName
     * @param array  $options
     *
     * @return bool
     * @throws InvalidOptionException
     */
    protected function isOptionEnabled($optionName, array $options)
    {
        if (!$this->isValidDataOption($optionName)) {
            throw new InvalidOptionException('"' . $optionName . '" is not a valid option.');
        }

        return (!empty($options[$optionName]) && $options[$optionName] === true);
    }

    /**
     * Returns true if an option is disabled and false otherwise
     *
     * @param string $optionName
     * @param array  $options
     *
     * @return bool
     */
    protected function isOptionDisabled($optionName, array $options)
    {
        return !$this->isOptionEnabled($optionName, $options);
    }

    /**
     * Converts a field name to a setter method name
     * (eg. name -> setName, name_en -> setNameEn)
     *
     * @param string $fieldName
     *
     * @return string
     */
    protected function fieldToSetterMethod($fieldName)
    {
        return 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $fieldName)));
    }

    /**
     * Returns true if a field is a foreign key and false otherwise.
     *
     * @param string $fieldName
     * @param array  $options
     *
     * @return bool
     */
    protected function isForeignKey($fieldName, array $options = array())
    {
        if (strncasecmp('fk_', $fieldName, 3) === 0) {
            return true;
        }

        return (
            !empty($options[static::DATA_OPTION_FOREIGN_KEYS]) &&
            in_array($fieldName, $options[static::DATA_OPTION_FOREIGN_KEYS])
        );
    }

    /**
     * Returns true if a field is a foreign key and false otherwise.
     *
     * @param string $fieldName
     * @param array  $options
     *
     * @return bool
     */
    protected function isLocalReference($fieldName, array $options = array())
    {
        return (
            !empty($options[static::DATA_OPTION_LOCAL_REFERENCES]) &&
            in_array($fieldName, $options[static::DATA_OPTION_LOCAL_REFERENCES])
        );
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function getOptions(array $data)
    {
        return isset($data['options']) ? $data['options'] : array();
    }

    /**
     * Loads all rows for a specific entity
     *
     * @param mixed         $entity
     * @param string        $key
     * @param ObjectManager $manager
     * @param array         $info
     * @param string        $referenceUniqueSuffix
     */
    protected function loadEntity($entity, $key, $manager, $info, $referenceUniqueSuffix = '')
    {
        $entityClass = get_class($entity);
        $options = $this->getOptions($info);

        foreach ($info['data'] as $itemIndex => $itemData) {
            $this->loadSingleEntity(
                $entityClass,
                $key,
                $manager,
                $itemIndex,
                $itemData,
                $options,
                $referenceUniqueSuffix
            );
        }
    }

    /**
     * Loads a single row for a specific entity
     *
     * @param string        $entityClass
     * @param string        $key
     * @param ObjectManager $manager
     * @param int           $itemIndex
     * @param array         $itemData
     * @param array         $options
     * @param string        $referenceUniqueSuffix
     */
    protected function loadSingleEntity(
        $entityClass,
        $key,
        ObjectManager $manager,
        $itemIndex,
        $itemData,
        $options,
        $referenceUniqueSuffix = ''
    ) {
        $entity = new $entityClass();

        foreach ($itemData as $fieldName => $fieldValue) {
            $value = $fieldValue;

            if ($this->isLocalReference($fieldName, $options)) {
                $value .= $referenceUniqueSuffix;
            }

            $this->setField($entity, $fieldName, $value, $options);
        }

        $manager->persist($entity);

        if ($this->isOptionEnabled(static::DATA_OPTION_FLUSH_ON_EVERY_ROW, $options)) {
            $this->flush($manager, $entityClass, $options);
        }

        if ($this->isOptionEnabled(static::DATA_OPTION_ADD_REFERENCE, $options)) {
            $referenceKey =
                implode(
                    static::LOCAL_REFERENCE_SEPARATOR,
                    array_filter(array($key, $itemIndex), 'static::referenceFilter')
                ) .
                $referenceUniqueSuffix;

            $this->addReference($referenceKey, $entity);
        }
    }

    /**
     * @param ObjectManager $manager
     * @param string        $entityClass
     * @param array         $options
     */
    protected function flush(ObjectManager $manager, $entityClass, array $options)
    {
        if ($this->isOptionEnabled(static::DATA_OPTION_FLUSH_PRESERVING_IDS, $options)) {
            $this->flushPreservingIds($manager, get_class(new $entityClass()));
        } else {
            $manager->flush();
        }
    }

    /**
     * @param $value
     *
     * @return bool
     */
    protected static function referenceFilter($value)
    {
        return ($value !== null && $value !== false && $value !== '');
    }

    /**
     * Triggers the DB write operation overriding the entity's generator type.
     * This is necessary for table entries for which part of our logic assumes have a certain ID.
     * (eg. sales order item statuses)
     *
     * @param ObjectManager $manager
     * @param string        $className Fully qualified class name (ie. with namespace)
     */
    protected function flushPreservingIds(ObjectManager $manager, $className)
    {
        /** @var ClassMetadata $metadata */
        $metadata = $manager->getClassMetaData($className);

        $defaultGenerator = $metadata->generatorType;

        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $manager->flush();

        // set generator type back to the default
        $metadata->setIdGeneratorType($defaultGenerator);
    }

    /**
     * Returns a unique suffix for local references
     *
     * @return string
     */
    protected function getUniqueSuffix()
    {
        return static::LOCAL_REFERENCE_SEPARATOR . uniqid();
    }
}
