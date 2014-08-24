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

    /** @var string The namespace to use when creating entities */
    protected $namespace;

    /** @var string The name of the fixture */
    protected $name;

    /** @var int The order in which the fixture should be loaded */
    protected $order;

    /** @var string The base directory for the fixture. The data will be loaded from the Data subdirectory. */
    protected $baseDir;

    /** @var array */
    protected $info;

    /** @var ContainerInterface */
    protected $container;

    /** @var mixed */
    protected $entity;

    /** @var array Available data options */
    protected $dataOptions = array(
        self::DATA_OPTION_FLUSH_PRESERVING_IDS,
        self::DATA_OPTION_ADD_REFERENCE,
        self::DATA_OPTION_FOREIGN_KEYS,
        self::DATA_OPTION_LOCAL_REFERENCES,
        self::DATA_OPTION_FLUSH_ON_EVERY_ROW
    );

    /**
     * Loads fixture info.
     *
     * @return ZanuiFixture
     */
    abstract public function loadInfo();

    /**
     * @param string $namespace
     *
     * @return ZanuiFixture
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return object
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        return $this->info;
    }

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
     * Sets a field of a given entity
     *
     * @param mixed  $entity
     * @param string $fieldName
     * @param string $fieldValue
     * @param array  $options
     */
    public function setField($entity, $fieldName, $fieldValue, array $options = array())
    {
        $setterName = $this->fieldToSetterMethod($fieldName);

        if ($this->isForeignKey($fieldName, $options)) {
            $entity->$setterName($this->getReference($fieldValue));
        } else {
            $entity->$setterName($fieldValue);
        }

        $this->entity = $entity;
    }

    /**
     * Asserts whether the given option is valid or not
     *
     * @param string $optionName
     *
     * @return bool
     */
    public function isValidDataOption($optionName)
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
    public function isOptionEnabled($optionName, array $options)
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
    public function isOptionDisabled($optionName, array $options)
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
    public function fieldToSetterMethod($fieldName)
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
    public function isForeignKey($fieldName, array $options = array())
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
    public function isLocalReference($fieldName, array $options = array())
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
    public function getOptions(array $data)
    {
        return isset($data['options']) ? $data['options'] : array();
    }

    /**
     * Loads all rows for a specific entity
     *
     * @param mixed         $entityClass
     * @param string        $key
     * @param ObjectManager $manager
     * @param array         $info
     * @param string        $referenceUniqueSuffix
     */
    public function loadEntity($entityClass, $key, $manager, $info, $referenceUniqueSuffix = '')
    {
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
    public function loadSingleEntity(
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
            $referenceKey = $this->getReferenceKey($key, $itemIndex, $referenceUniqueSuffix);
            $this->addReference($referenceKey, $entity);
        }

        $this->entity = $entity;
    }

    /**
     * @param ObjectManager $manager
     * @param string        $entityClass Fully qualified class name (ie. with namespace)
     * @param array         $options
     */
    public function flush(ObjectManager $manager, $entityClass, array $options)
    {
        if ($this->isOptionEnabled(static::DATA_OPTION_FLUSH_PRESERVING_IDS, $options)) {
            $this->flushPreservingIds($manager, $entityClass);
        } else {
            $manager->flush();
        }
    }

    /**
     * Triggers the DB write operation overriding the entity's generator type.
     * This is necessary for table entries for which part of our logic assumes have a certain ID.
     * (eg. sales order item statuses)
     *
     * @param ObjectManager $manager
     * @param string        $className Fully qualified class name (ie. with namespace)
     */
    public function flushPreservingIds(ObjectManager $manager, $className)
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
     * @param string $key
     * @param string $itemIndex
     * @param string $referenceUniqueSuffix
     *
     * @return string
     */
    public function getReferenceKey($key, $itemIndex, $referenceUniqueSuffix)
    {
        return implode(
            static::LOCAL_REFERENCE_SEPARATOR,
            array_filter(
                array($key, $itemIndex),
                function ($value) {
                    return ($value !== null && $value !== false && $value !== '');
                }
            )
        ) .
        $referenceUniqueSuffix;
    }

    /**
     * Returns a unique suffix for local references
     *
     * @return string
     */
    public function generateUniqueSuffix()
    {
        return static::LOCAL_REFERENCE_SEPARATOR . uniqid();
    }
}
