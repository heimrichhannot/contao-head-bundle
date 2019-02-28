<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Test\Manager;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\HeadBundle\Head\TagInterface;
use HeimrichHannot\HeadBundle\Manager\TagManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class TagManagerTest extends ContaoTestCase
{
    public function setUp()
    {
        parent::setUp();

        if (!\defined('TL_ROOT')) {
            \define('TL_ROOT', '/src');
        }

        $container = new ContainerBuilder(new ParameterBag(['kernel.cache_dir' => false]));
        System::setContainer($container);
    }

    /**
     * test registration of tag.
     */
    public function testRegisterTag()
    {
        $tagManager = new TagManager();
        $tag = $this->createMock(TagInterface::class);
        $tagManager->registerTag($tag);

        $tags = $this->getPrivateProperty($tagManager);

        $this->assertNotEmpty($tags);
        $this->assertCount(1, $tags);
        $this->assertArrayHasKey(\get_class($tag), $tags);
    }

    /**
     * test getting of tags as array.
     */
    public function testGetTags()
    {
        $tagManager = new TagManager();

        $tag = $this->createMock(TagInterface::class);
        $tag->method('hasContent')->willReturn(true);
        $tag->method('getContent')->willReturn('tag');
        $tag->method('generate')->willReturn('tag');

        $tagManager->registerTag($tag);

        $tags = $tagManager->getTags();

        $this->assertNotEmpty($tags);
        $this->assertCount(1, $tags);
        $this->assertArrayNotHasKey(\get_class($tag), $tags);

        $tag = $this->createMock(TagInterface::class);
        $tag->method('hasContent')->willReturn(false);

        $tagManager->registerTag($tag);

        $tags = $tagManager->getTags();

        $this->assertEmpty($tags);
    }

    /**
     * access and return a private property.
     *
     * @param $object
     *
     * @throws \ReflectionException
     *
     * @return mixed
     */
    public function getPrivateProperty($object)
    {
        $reflector = new \ReflectionClass($object);
        $property = $reflector->getProperty('tags');
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
