<?php

/*
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace Contao\CalendarBundle\Tests\DependencyInjection;

use HeimrichHannot\HeadBundle\DependencyInjection\HeimrichHannotContaoHeadExtension;
use HeimrichHannot\HeadBundle\EventListener\HookListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class HeimrichHannotContaoHeadExtensionTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->container = new ContainerBuilder(new ParameterBag(['kernel.debug' => false]));

        $extension = new HeimrichHannotContaoHeadExtension();
        $extension->load([], $this->container);
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $extension = new HeimrichHannotContaoHeadExtension();

        $this->assertInstanceOf(HeimrichHannotContaoHeadExtension::class, $extension);
    }

    /**
     * Tests the huh.head.listener.hooks service.
     */
    public function testRegistersTheHookListener()
    {
        $this->assertTrue($this->container->has('huh.head.listener.hooks'));

        $definition = $this->container->getDefinition('huh.head.listener.hooks');

        $this->assertSame(HookListener::class, $definition->getClass());
        $this->assertSame('contao.framework', (string) $definition->getArgument(0));
    }
}
