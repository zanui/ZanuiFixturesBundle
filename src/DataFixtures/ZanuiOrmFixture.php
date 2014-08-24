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

    /** @var mixed */
    protected $dataFileContent;

    /**
     * @param mixed $dataFileContent
     *
     * @return ZanuiOrmFixture
     */
    public function setDataFileContent($dataFileContent)
    {
        $this->dataFileContent = $dataFileContent;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDataFileContent()
    {
        return $this->dataFileContent;
    }

    /**
     * @param string $dataFilename
     *
     * @return ZanuiOrmFixture
     */
    public function setDataFilename($dataFilename)
    {
        $this->dataFilename = $dataFilename;

        return $this;
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
        $this->loadEntity($entityClass, $this->name, $manager, $this->info);

        if ($this->isOptionDisabled(static::DATA_OPTION_FLUSH_ON_EVERY_ROW, $options)) {
            $this->flush($manager, $entityClass, $options);
        }
    }

    /**
     * @return ZanuiOrmFixture
     */
    public function loadDataFilename()
    {
        if (!isset($this->dataFilename)) {
            $this->dataFilename =
                $this->baseDir . DIRECTORY_SEPARATOR .
                'Data' . DIRECTORY_SEPARATOR .
                $this->name . '.yml';
        }

        return $this;
    }

    /**
     * @param string $filename
     */
    public function parseDataFileContent($filename)
    {
        if (isset($this->dataFileContent)) {
            return;
        }

        $file = new SplFileInfo($filename, '', '');

        $this->dataFileContent = Yaml::parse($file->getContents());
    }

    /**
     * {@inheritdoc}
     */
    public function loadInfo()
    {
        $filename = $this->getDataFilename();
        $this->parseDataFileContent($filename);
        $fileContents = $this->getDataFileContent();

        if (!is_array($fileContents)) {
            throw new LoadInfoException('File ' . $filename . ' could not be parsed into an array.');
        }

        if (!isset($fileContents['data'])) {
            throw new LoadInfoException('File ' . $filename . ' does not have a data key.');
        }

        $this->info = $fileContents;
    }
}
