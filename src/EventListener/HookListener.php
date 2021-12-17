<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\EventListener;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Environment;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use Contao\Template;
use HeimrichHannot\HeadBundle\Manager\TagManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class HookListener
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
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor.
     */
    public function __construct(ContainerInterface $container, ContaoFrameworkInterface $framework, TagManager $tagManager)
    {
        $this->framework = $framework;
        $this->tagManager = $tagManager;
        $this->container = $container;
    }

    /**
     * Modify the page object.
     */
    public function parseTemplate(Template $template)
    {
        $template->meta = function (array $skip = []) {
            return implode("\n", $this->tagManager->getTags($skip));
        };
    }

    /**
     * Modify the page layout.
     */
    public function getPageLayout(PageModel $page, LayoutModel $layout, PageRegular $pageRegular)
    {
        $titleTag = $layout->titleTag;

        $this->container->get('huh.head.tag.meta_charset')->setContent(Config::get('characterSet'));
        $this->container->get('huh.head.tag.base')->setContent(Environment::get('base'));

        // prepare data
        $description = str_replace(["\n", "\r", '"'], [' ', '', ''], $page->description);

        if ('' === $titleTag) {
            $firstPage = $this->framework->getAdapter(PageModel::class)->findFirstPublishedByPid($page->rootId);

            $title = '{{page::rootPageTitle}}';

            // add pageTitle only if not first page / front page)
            if (null === $firstPage || $firstPage->id !== $page->id || isset($_GET['auto_item'])) {
                $title = '{{page::pageTitle}} - '.$title;
            }

            $titleTag = $title;
        }

        // image
        $image = null;

        if (null !== ($rootPage = $this->framework->getAdapter(PageModel::class)->findByPk($page->rootId ?: $page->id))) {
            if ($rootPage->addHeadDefaultImage && $rootPage->headDefaultImage) {
                if ($imageTmp = $this->container->get('huh.utils.file')->getPathFromUuid($rootPage->headDefaultImage)) {
                    $image = Environment::get('url').'/'.$imageTmp;
                }
            }
        }

        $path = Request::createFromGlobals()->getPathInfo(); // path without query string
        $url = Environment::get('url').$path;

        // if path is id, take absolute url from current page
        if (is_numeric(ltrim($path, '/'))) {
            $url = $page->getAbsoluteUrl();
        }

        if ($this->container->get('huh.request')->hasGet('full')) {
            $url = $this->container->get('huh.utils.url')->getCurrentUrl([]);
        }

        // title tag
        $this->container->get('huh.head.tag.title')->setContent($titleTag);

        // default meta data
        $this->container->get('huh.head.tag.meta_language')->setContent($this->container->get('translator')->getLocale());
        $this->container->get('huh.head.tag.meta_description')->setContent($description);
        $this->container->get('huh.head.tag.meta_robots')->setContent($page->robots ?: 'index,follow');

        // default twitter card
        $this->container->get('huh.head.tag.twitter_card')->setContent('summary');
        $this->container->get('huh.head.tag.twitter_title')->setContent($titleTag);
        $this->container->get('huh.head.tag.twitter_description')->setContent($description);

        if ($image) {
            $this->container->get('huh.head.tag.twitter_image')->setContent($image);
        }

        if ($rootPage->twitterSite) {
            $this->container->get('huh.head.tag.twitter_creator')->setContent($rootPage->twitterSite);
        }

        // default open graph data
        $this->container->get('huh.head.tag.og_title')->setContent($titleTag);
        $this->container->get('huh.head.tag.og_description')->setContent($description);
        $this->container->get('huh.head.tag.og_url')->setContent($url);

        if ($image) {
            $this->container->get('huh.head.tag.og_image')->setContent($image);
        }

        // canonical
        $this->container->get('huh.head.tag.link_canonical')->setContent($url);
    }
}
