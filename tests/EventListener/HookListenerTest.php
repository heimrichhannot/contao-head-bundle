<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Test\EventListener;

use Contao\Config;
use Contao\CoreBundle\Config\ResourceFinder;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Environment;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use HeimrichHannot\HeadBundle\EventListener\HookListener;
use HeimrichHannot\HeadBundle\Manager\TagManager;
use HeimrichHannot\HeadBundle\Tag\Link\LinkCanonical;
use HeimrichHannot\HeadBundle\Tag\Meta\MetaCharset;
use HeimrichHannot\HeadBundle\Tag\Meta\MetaDescription;
use HeimrichHannot\HeadBundle\Tag\Meta\MetaLanguage;
use HeimrichHannot\HeadBundle\Tag\Meta\MetaRobots;
use HeimrichHannot\HeadBundle\Tag\Meta\OG\OGDescription;
use HeimrichHannot\HeadBundle\Tag\Meta\OG\OGImage;
use HeimrichHannot\HeadBundle\Tag\Meta\OG\OGTitle;
use HeimrichHannot\HeadBundle\Tag\Meta\OG\OGUrl;
use HeimrichHannot\HeadBundle\Tag\Meta\Twitter\TwitterCard;
use HeimrichHannot\HeadBundle\Tag\Meta\Twitter\TwitterImage;
use HeimrichHannot\HeadBundle\Tag\Meta\Twitter\TwitterTitle;
use HeimrichHannot\HeadBundle\Tag\Misc\Base;
use HeimrichHannot\HeadBundle\Tag\Misc\Title;
use HeimrichHannot\UtilsBundle\File\FileUtil;
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

        if (!\defined('TL_ROOT')) {
            \define('TL_ROOT', __DIR__);
        }

        $container = $this->mockContainer();

        $database = $this->createMock(Connection::class);
        $container->set('database_connection', $database);

        $requestStack = $this->createRequestStackMock();
        $container->set('request_stack', $requestStack);

        $adapter = $this->getAdapter();
        $this->framework = $this->mockContaoFramework([PageModel::class => $adapter]);

        $container->set('contao.framework', $this->framework);
        $container->set('contao.resource_finder', new ResourceFinder([]));
        $container->set('translator', new Translator('de'));

        $this->tagManager = $this->createMock(TagManager::class);
        $this->tagManager->method('getTags')->willReturn(['value1', 'value2', 'value3']);

        $container->set('huh.head.tag.meta_charset', new MetaCharset($this->tagManager));
        $container->set('huh.head.tag.base', new Base($this->tagManager));
        $container->set('huh.head.tag.title', new Title($this->tagManager));
        $container->set('huh.head.tag.meta_language', new MetaLanguage($this->tagManager));
        $container->set('huh.head.tag.meta_description', new MetaDescription($this->tagManager));
        $container->set('huh.head.tag.meta_robots', new MetaRobots($this->tagManager));
        $container->set('huh.head.tag.link_canonical', new LinkCanonical($this->tagManager));
        $container->set('huh.head.tag.twitter_card', new TwitterCard($this->tagManager));
        $container->set('huh.head.tag.twitter_title', new TwitterTitle($this->tagManager));
        $container->set('huh.head.tag.twitter_description', new TwitterTitle($this->tagManager));
        $container->set('huh.head.tag.twitter_image', new TwitterImage($this->tagManager));
        $container->set('huh.head.tag.og_title', new OGTitle($this->tagManager));
        $container->set('huh.head.tag.og_description', new OGDescription($this->tagManager));
        $container->set('huh.head.tag.og_url', new OGUrl($this->tagManager));
        $container->set('huh.head.tag.og_image', new OGImage($this->tagManager));

        $fileUtil = $this->createConfiguredMock(FileUtil::class, [
            'getPathFromUuid' => 'files/images/myImage.png',
        ]);
        $container->set('huh.utils.file', $fileUtil);

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
        $page = $this->getPageModel();

        $layout = $this->getLayoutModel();

        $pageRegular = $this->getPageRegularModel();

        $listener = new HookListener($this->framework, $this->tagManager);
        $listener->generatePage($page, $layout, $pageRegular);

        $this->assertSame('value1'.PHP_EOL.'value2'.PHP_EOL.'value3', $pageRegular->Template->meta);
    }

    /**
     * test modifying of page layout.
     */
    public function testGetPageLayout()
    {
        $page = $this->getPageModel();

        $layout = $this->getLayoutModel('title');
        $pageRegular = $this->getPageRegularModel();

        $GLOBALS['objPage'] = $this->getPageModel();

        $listener = new HookListener($this->framework, $this->tagManager);
        $listener->getPageLayout($page, $layout, $pageRegular);

        $container = System::getContainer();

        $this->assertSame(Config::get('characterSet'), $container->get('huh.head.tag.meta_charset')->getContent());
        $this->assertSame(Environment::get('base'), $container->get('huh.head.tag.base')->getContent());
        $this->assertSame('title', $container->get('huh.head.tag.title')->getContent());
        $this->assertSame('de', $container->get('huh.head.tag.meta_language')->getContent());
        $this->assertSame('description', $container->get('huh.head.tag.meta_description')->getContent());
        $this->assertSame('index,follow', $container->get('huh.head.tag.meta_robots')->getContent());
        $this->assertSame('http://localhost/', $container->get('huh.head.tag.link_canonical')->getContent());

        $adapter = $this->mockAdapter(['findFirstPublishedByPid', 'findByPk']);
        $adapter->method('findFirstPublishedByPid')->willReturn(null);
        $adapter->method('findByPk')->willReturn(null);
        $this->framework = $this->mockContaoFramework([PageModel::class => $adapter]);
        $container->set('contao.framework', $this->framework);
        System::setContainer($container);

        $layout = $this->getLayoutModel('');
        $listener = new HookListener($this->framework, $this->tagManager);
        $listener->getPageLayout($page, $layout, $pageRegular);

        $this->assertNotEmpty($container->get('huh.head.tag.title')->getContent());
        $this->assertSame('{{page::pageTitle}} - {{page::rootPageTitle}}', $container->get('huh.head.tag.title')->getContent());

        $adapter = $this->getAdapter();
        $this->framework = $this->mockContaoFramework([PageModel::class => $adapter]);
        $container->set('contao.framework', $this->framework);
        System::setContainer($container);

        $listener = new HookListener($this->framework, $this->tagManager);
        $listener->getPageLayout($page, $layout, $pageRegular);

        $this->assertNotEmpty($container->get('huh.head.tag.title')->getContent());
        $this->assertSame('{{page::rootPageTitle}}', $container->get('huh.head.tag.title')->getContent());
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
     * @return LayoutModel
     */
    public function getLayoutModel(string $title = '')
    {
        return $this->mockClassWithProperties(LayoutModel::class, ['id' => 1, 'titleTag' => $title]);
    }

    /**
     * @return \LayoutModel
     */
    public function getPageModel()
    {
        $page = $this->mockClassWithProperties(PageModel::class, ['id' => 1, 'description' => 'description']);
        $page->method('getAbsoluteUrl')->willReturn('localhost');

        return $page;
    }

    public function createRequestStackMock()
    {
        $requestStack = new RequestStack();
        $request = new \Symfony\Component\HttpFoundation\Request();
        $request->attributes->set('_contao_referer_id', 'foobar');
        $requestStack->push($request);

        return $requestStack;
    }

    public function getAdapter()
    {
        $rootPage = $this->mockClassWithProperties(
            PageModel::class,
            [
                'id' => 1,
                'titleTag' => 'rootPage',
                'addHeadDefaultImage' => true,
                'headDefaultImage' => 'myImage',
            ]
        );

        $adapter = $this->mockAdapter(['findFirstPublishedByPid', 'findByPk']);
        $adapter->method('findFirstPublishedByPid')->willReturn($rootPage);
        $adapter->method('findByPk')->willReturn($rootPage);

        return $adapter;
    }
}
