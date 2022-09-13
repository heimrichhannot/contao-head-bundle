<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\EventListener;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Environment;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class HookListener
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var ContainerInterface
     */
    private $container;
    private Utils $utils;

    /**
     * Constructor.
     */
    public function __construct(ContainerInterface $container, ContaoFrameworkInterface $framework, Utils $utils)
    {
        $this->framework = $framework;
        $this->container = $container;
        $this->utils = $utils;
    }

    /**
     * Modify the page layout.
     */
    public function getPageLayout(PageModel $page, LayoutModel $layout, PageRegular $pageRegular)
    {
        $titleTag = $layout->titleTag;

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

        $path = Request::createFromGlobals()->getPathInfo(); // path without query string
        $url = Environment::get('url').$path;

        // if path is id, take absolute url from current page
        if (is_numeric(ltrim($path, '/'))) {
            $url = $page->getAbsoluteUrl();
        }

        if ($this->container->get('huh.request')->hasGet('full')) {
            $url = $this->container->get('huh.utils.url')->getCurrentUrl([]);
        }

        // default meta data
        $this->container->get('huh.head.tag.meta_language')->setContent($this->container->get('translator')->getLocale());
        // default twitter card
        $this->container->get('huh.head.tag.twitter_card')->setContent('summary');
        $this->container->get('huh.head.tag.twitter_title')->setContent($titleTag);
        $this->container->get('huh.head.tag.twitter_description')->setContent($description);

        // default open graph data
        $this->container->get('huh.head.tag.og_title')->setContent($titleTag);
        $this->container->get('huh.head.tag.og_description')->setContent($description);
        $this->container->get('huh.head.tag.og_url')->setContent($url);
    }
}
