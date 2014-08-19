<?php
/**
 * Zanui (http://www.zanui.com.au/)
 *
 * @link      http://github.com/zanui/shop for the canonical source repository
 * @copyright Copyright (c) 2011-2014 Internet Services Australia 3 Pty Limited (http://www.zanui.com.au)
 * @license   The MIT License (MIT)
 */

namespace Zanui\FixturesBundle\DataFixtures;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo as FinderSplFileInfo;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Zanui Custom Loader
 *
 * Defines functions to facilitate custom loaders
 */
abstract class ZanuiCustomLoader extends ZanuiFixture
{
    /** @var ObjectManager */
    protected $manager;

    /** @var string */
    protected $referenceUniqueSuffix;

    /** @var array */
    protected $info;

    /** @var array */
    protected $current;

    /**
     * {@inheritdoc}
     */
    protected function loadInfo($dir = null, $name = null)
    {
        if ($dir === null) {
            $dir = $this->baseDir;
        }

        if ($name === null) {
            $name = $this->name;
        }

        $finder = new Finder();
        $finder->files()->in($dir . '/' . $name)->name('*.yml');

        $customInfo = array();

        foreach ($finder as $file) {
            /** @var FinderSplFileInfo $file */

            $customInfo[$file->getFilename()] = Yaml::parse($file->getContents());
        }

        return $customInfo;
    }

    /**
     * @param mixed  $entity
     * @param string $tableName
     */
    protected function loadCustomEntity($entity, $tableName)
    {
        parent::loadEntity(
            $entity,
            $tableName,
            $this->manager,
            $this->current['data'][$tableName],
            $this->referenceUniqueSuffix
        );
    }
}
