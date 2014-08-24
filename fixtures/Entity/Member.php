<?php
/**
 * Zanui (http://www.zanui.com.au/)
 *
 * @link      http://github.com/zanui/shop for the canonical source repository
 * @copyright Copyright (c) 2011-2014 Internet Services Australia 3 Pty Limited (http://www.zanui.com.au)
 * @license   The MIT License (MIT)
 */

namespace fixtures\Entity;

class Member
{
    /** @var Team */
    protected $team;

    /** @var string */
    protected $name;

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

    /**
     * @param Team $team
     */
    public function setTeam($team)
    {
        $this->team = $team;
    }

    /**
     * @return Team
     */
    public function getTeam()
    {
        return $this->team;
    }
}