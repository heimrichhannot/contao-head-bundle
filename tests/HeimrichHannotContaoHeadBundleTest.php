<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\HeadBundle\Tests;

use HeimrichHannot\HeadBundle\DependencyInjection\HeimrichHannotContaoHeadExtension;
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

    /**
     * test the return of extension.
     */
    public function testGetContainerExtension()
    {
        $bundle = new HeimrichHannotContaoHeadBundle();

        $extension = $bundle->getContainerExtension();

        $this->assertInstanceOf(HeimrichHannotContaoHeadExtension::class, $extension);
    }
}
