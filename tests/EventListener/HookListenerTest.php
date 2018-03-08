<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\HeadBundle\Test\EventListener;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Environment;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\HeadBundle\EventListener\HookListener;
use HeimrichHannot\HeadBundle\Manager\TagManager;
use HeimrichHannot\HeadBundle\Tag\Link\LinkCanonical;
use HeimrichHannot\HeadBundle\Tag\Meta\MetaCharset;
use HeimrichHannot\HeadBundle\Tag\Meta\MetaDescription;
use HeimrichHannot\HeadBundle\Tag\Meta\MetaLanguage;
use HeimrichHannot\HeadBundle\Tag\Meta\MetaRobots;
use HeimrichHannot\HeadBundle\Tag\Misc\Base;
use HeimrichHannot\HeadBundle\Tag\Misc\Title;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\Translator;

class HookListenerTest extends ContaoTestCase
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var TagManager
     */
    private $tagManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        if (!defined('TL_ROOT')) {
            define('TL_ROOT', '/src');
        }

        $this->framework = $this->mockContaoFramework();
        $this->tagManager = $this->createMock(TagManager::class);
        $this->tagManager->method('getTags')->willReturn(['value1', 'value2', 'value3']);

        $container = new ContainerBuilder(new ParameterBag(['kernel.cache_dir' => false]));
        $container->set('huh.head.tag.meta_charset', new MetaCharset($this->tagManager));
        $container->set('huh.head.tag.base', new Base($this->tagManager));
        $container->set('huh.head.tag.title', new Title($this->tagManager));
        $container->set('huh.head.tag.meta_language', new MetaLanguage($this->tagManager));
        $container->set('huh.head.tag.meta_description', new MetaDescription($this->tagManager));
        $container->set('huh.head.tag.meta_robots', new MetaRobots($this->tagManager));
        $container->set('huh.head.tag.link_canonical', new LinkCanonical($this->tagManager));

        $container->set('request_stack', new RequestStack());
        $container->set('translator', new Translator('de'));

        System::setContainer($container);
    }

    /**
     * Test objects instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf(HookListener::class, new HookListener($this->framework, $this->tagManager));
    }

    public function testGeneratePage()
    {
        $pageRegular = $this->getPageRegularModel();

        $listener = new HookListener($this->framework, $this->tagManager);
        $listener->generatePage($pageRegular);

        $this->assertSame('value1'.PHP_EOL.'value2'.PHP_EOL.'value3', $pageRegular->Template->meta);
    }

    /**
     * test modifying of page layout.
     */
    public function testGetPageLayout()
    {
        $layout = $this->getLayoutModel('title');

        $GLOBALS['objPage'] = $this->getPageModel();

        $listener = new HookListener($this->framework, $this->tagManager);
        $listener->getPageLayout($layout);

        $container = System::getContainer();

        $this->assertSame(Config::get('characterSet'), $container->get('huh.head.tag.meta_charset')->getContent());
        $this->assertSame(Environment::get('base'), $container->get('huh.head.tag.base')->getContent());
        $this->assertSame('title', $container->get('huh.head.tag.title')->getContent());
        $this->assertSame('de', $container->get('huh.head.tag.meta_language')->getContent());
        $this->assertSame('description', $container->get('huh.head.tag.meta_description')->getContent());
        $this->assertSame('index,follow', $container->get('huh.head.tag.meta_robots')->getContent());
        $this->assertSame('http://localhost/', $container->get('huh.head.tag.link_canonical')->getContent());
    }

    /**
     * @return \PageRegular
     */
    public function getPageRegularModel()
    {
        $pageRegular = $this->createMock(PageRegular::class);
        $pageRegular->Template = new \stdClass();
        $pageRegular->Template->meta = '';

        return $pageRegular;
    }

    /**
     * @param string $title
     *
     * @return \LayoutModel
     */
    public function getLayoutModel(string $title)
    {
        return $this->mockClassWithProperties(LayoutModel::class, ['titleTag' => $title]);
    }

    /**
     * @return \LayoutModel
     */
    public function getPageModel()
    {
        $page = $this->mockClassWithProperties(PageModel::class, ['description' => 'description']);
        $page->method('getAbsoluteUrl')->willReturn('localhost');

        return $page;
    }
}
