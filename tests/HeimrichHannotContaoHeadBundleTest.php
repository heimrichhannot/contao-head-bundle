<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Tests;

use HeimrichHannot\HeadBundle\DependencyInjection\HeimrichHannotContaoHeadExtension;
use HeimrichHannot\HeadBundle\HeimrichHannotContaoHeadBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
     * test build of bundle.
     */
    public function testBuild()
    {
        $container = new ContainerBuilder();
        $bundle = new HeimrichHannotContaoHeadBundle();

        $bundle->build($container);

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
