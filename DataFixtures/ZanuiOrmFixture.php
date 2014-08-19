<?php
/**
 * Zanui (http://www.zanui.com.au/)
 *
 * @link      http://github.com/zanui/shop for the canonical source repository
 * @copyright Copyright (c) 2011-2014 Internet Services Australia 3 Pty Limited (http://www.zanui.com.au)
 * @license   The MIT License (MIT)
 */

namespace Zanui\FixturesBundle\DataFixtures;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Zanui ORM fixture
 *
 * Used for fixtures that load data into a single table.
 * It is abstract because it does not implement the load method.
 */
abstract class ZanuiOrmFixture extends ZanuiFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $info = $this->loadInfo();
        $options = $this->getOptions($info);
        $entityClass = $this->namespace . '\\' . $this->name;

        foreach ($info['data'] as $key => $itemData) {
            $this->loadSingleEntity(
                $entityClass,
                $key,
                $manager,
                '',
                $itemData,
                $options
            );
        }

        if ($this->isOptionDisabled(static::DATA_OPTION_FLUSH_ON_EVERY_ROW, $options)) {
            $this->flush($manager, $entityClass, $options);
        }
    }

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

        $filename = $dir . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . $name . '.yml';
        $file = new SplFileInfo($filename, '', '');

        return Yaml::parse($file->getContents());
    }
}
