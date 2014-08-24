<?php
/**
 * Zanui (http://www.zanui.com.au/)
 *
 * @link      http://github.com/zanui/shop for the canonical source repository
 * @copyright Copyright (c) 2011-2014 Internet Services Australia 3 Pty Limited (http://www.zanui.com.au)
 * @license   The MIT License (MIT)
 */

namespace fixtures\Entity;

class Team
{
    /** @var string */
    protected $name;

    /** @var string */
    protected $motto;

    /**
     * @param string $motto
     */
    public function setMotto($motto)
    {
        $this->motto = $motto;
    }

    /**
     * @return string
     */
    public function getMotto()
    {
        return $this->motto;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
