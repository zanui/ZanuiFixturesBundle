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
    protected $current;

    /**
     * {@inheritdoc}
     */
    public function loadInfo()
    {
        $finder = new Finder();
        $finder->files()->in($this->baseDir . '/' . $this->name)->name('*.yml');

        $customInfo = array();

        foreach ($finder as $file) {
            /** @var FinderSplFileInfo $file */

            $customInfo[$file->getFilename()] = Yaml::parse($file->getContents());
        }

        $this->info = $customInfo;

        return $customInfo;
    }

    /**
     * @param string $entityClass
     * @param string $tableName
     */
    public function loadCustomEntity($entityClass, $tableName)
    {
        $this->loadEntity(
            $entityClass,
            $tableName,
            $this->manager,
            $this->current['data'][$tableName],
            $this->referenceUniqueSuffix
        );
    }
}
