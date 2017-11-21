<?php

/*
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\HeadBundle\Tests;

use HeimrichHannot\HeadBundle\HeimrichHannotContaoHeadBundle;
use PHPUnit\Framework\TestCase;

class HeimrichHannotContaoHeadBundleTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $bundle = new HeimrichHannotContaoHeadBundle();

        $this->assertInstanceOf(HeimrichHannotContaoHeadBundle::class, $bundle);
    }
}
