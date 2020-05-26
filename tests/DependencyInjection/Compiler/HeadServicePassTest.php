<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Tests\DependencyInjection\Compiler;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\HeadBundle\DependencyInjection\Compiler\HeadServicePass;
use HeimrichHannot\HeadBundle\Tag\Misc\Title;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class HeadServicePassTest extends ContaoTestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $compiler = new HeadServicePass();

        $this->assertInstanceOf(HeadServicePass::class, $compiler);
    }

    /**
     * Test process().
     */
    public function testProcess()
    {
        $container = new ContainerBuilder();

        $definition = new Definition();
        $definition->setPublic(true);
        $definition->setClass(Title::class);
        $definition->setArguments(['@huh.head.tag_manager']);

        $container->addDefinitions(['huh.head.tag.title' => $definition]);

        $compiler = new HeadServicePass();
        $compiler->process($container);

        $this->assertSame(['huh.head.tag.title'], $container->getParameter('huh.head.tags'));
    }
}
