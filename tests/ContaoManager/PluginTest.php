<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Test\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\DelegatingParser;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\HeadBundle\ContaoManager\Plugin;
use HeimrichHannot\HeadBundle\HeimrichHannotContaoHeadBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Test the plugin class
 * Class PluginTest.
 */
class PluginTest extends ContaoTestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        static::assertInstanceOf(Plugin::class, new Plugin());
    }

    /**
     * Tests the bundle contao invocation.
     */
    public function testGetBundles()
    {
        $plugin = new Plugin();

        /** @var BundleConfig[] $bundles */
        $bundles = $plugin->getBundles(new DelegatingParser());

        static::assertCount(1, $bundles);
        static::assertInstanceOf(BundleConfig::class, $bundles[0]);
        static::assertEquals(HeimrichHannotContaoHeadBundle::class, $bundles[0]->getName());
        static::assertEquals([ContaoCoreBundle::class, 'modal'], $bundles[0]->getLoadAfter());
    }

    public function testGetRouteCollection()
    {
        define('TL_ROOT', 'src');

        $plugin = new Plugin();

        $kernel = $this->createMock(KernelInterface::class);
        $resolver = $this->getMockBuilder(LoaderResolverInterface::class)->getMock();
        $resolvedLoader = $this->getMockBuilder(LoaderInterface::class)->getMock();

        $resolver->expects($this->once())
            ->method('resolve')
            ->will($this->returnValue($resolvedLoader));

        $originalRoute = new Route(TL_ROOT.'/Resources/config/routing.yml');
        $expectedCollection = new RouteCollection();
        $expectedCollection->add('one_test_route', $originalRoute);
        $expectedCollection->addResource(new FileResource(TL_ROOT.'/Resources/config/routing.yml'));

        $resolvedLoader
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($expectedCollection));

        $loader = $this->getMockBuilder(LoaderInterface::class)->getMock();
        $loader->expects($this->any())
            ->method('getResolver')
            ->will($this->returnValue($resolver));

        $collection = $plugin->getRouteCollection($resolver, $kernel);

        $this->assertInstanceOf(RouteCollection::class, $collection);
    }
}
