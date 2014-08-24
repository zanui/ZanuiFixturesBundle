<?php
/**
 * Zanui (http://www.zanui.com.au/)
 *
 * @link      http://github.com/zanui/shop for the canonical source repository
 * @copyright Copyright (c) 2011-2014 Internet Services Australia 3 Pty Limited (http://www.zanui.com.au)
 * @license   The MIT License (MIT)
 */

namespace fixtures\Entity;

class EntityA
{
    protected $fieldA;
    protected $fieldB;

    /**
     * @param mixed $fieldA
     */
    public function setFieldA($fieldA)
    {
        $this->fieldA = $fieldA;
    }

    /**
     * @return mixed
     */
    public function getFieldA()
    {
        return $this->fieldA;
    }

    /**
     * @param mixed $fieldB
     */
    public function setFieldB($fieldB)
    {
        $this->fieldB = $fieldB;
    }

    /**
     * @return mixed
     */
    public function getFieldB()
    {
        return $this->fieldB;
    }
}
