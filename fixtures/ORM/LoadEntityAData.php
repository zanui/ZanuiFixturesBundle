<?php
/**
 * Zanui (http://www.zanui.com.au/)
 *
 * @link      http://github.com/zanui/shop for the canonical source repository
 * @copyright Copyright (c) 2011-2014 Internet Services Australia 3 Pty Limited (http://www.zanui.com.au)
 * @license   The MIT License (MIT)
 */

namespace fixtures\ORM;

use Zanui\FixturesBundle\DataFixtures\ZanuiOrmFixture;

class LoadEntityAData extends ZanuiOrmFixture
{
    protected $namespace = 'fixtures\Entity';
    protected $name = 'EntityA';
    protected $baseDir = __DIR__;
}
