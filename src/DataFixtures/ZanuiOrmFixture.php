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
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;
use Zanui\FixturesBundle\Exception\LoadInfoException;

/**
 * Zanui ORM fixture
 *
 * Used for fixtures that load data into a single table.
 * It is abstract because it does not implement the load method.
 */
abstract class ZanuiOrmFixture extends ZanuiFixture
{
    /** @var string */
    protected $dataFilename;

    /**
     * @return array
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @return string
     */
    public function getDataFilename()
    {
        return $this->dataFilename;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->loadDataFilename();
        $this->loadInfo();
        $options = $this->getOptions($this->info);

        $entityClass = $this->namespace . '\\' . $this->name;

        foreach ($this->info['data'] as $key => $itemData) {
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
     * @return ZanuiOrmFixture
     */
    public function loadDataFilename()
    {
        $this->dataFilename =
            $this->baseDir . DIRECTORY_SEPARATOR .
            'Data' . DIRECTORY_SEPARATOR .
            $this->name . '.yml';

        return $this;
    }

    /**
     * @param string $filename
     * @return string
     */
    public function getDataFileContent($filename)
    {
        $file = new SplFileInfo($filename, '', '');

        return $file->getContents();
    }

    /**
     * @throws LoadInfoException
     */
    protected function loadInfo()
    {
        $filename = $this->getDataFilename();
        $fileContents = Yaml::parse($this->getDataFileContent($filename));

        if (!is_array($fileContents)) {
            throw new LoadInfoException('File ' . $filename . ' could not be parsed into an array.');
        }

        if (!isset($fileContents['data'])) {
            throw new LoadInfoException('File ' . $filename . ' does not have a data key.');
        }

        $this->info = $fileContents;
    }
}
