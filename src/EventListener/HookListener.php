<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
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
use Contao\System;
use HeimrichHannot\HeadBundle\Manager\TagManager;
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
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework, TagManager $tagManager)
    {
        $this->framework = $framework;
        $this->tagManager = $tagManager;
    }

    /**
     * Modify the page object.
     *
     * @param PageModel   $page
     * @param LayoutModel $layout
     * @param PageRegular $pageRegular
     */
    public function generatePage(PageModel $page, LayoutModel $layout, PageRegular $pageRegular)
    {
        $pageRegular->Template->meta = implode("\n", $this->tagManager->getTags());
    }

    /**
     * Modify the page layout.
     *
     * @param PageModel   $page
     * @param LayoutModel $layout
     * @param PageRegular $pageRegular
     */
    public function getPageLayout(PageModel $page, LayoutModel $layout, PageRegular $pageRegular)
    {
        /*
         * @var $objPage \Contao\PageModel
         */
        global $objPage;

        $titleTag = $layout->titleTag;

        System::getContainer()->get('huh.head.tag.meta_charset')->setContent(Config::get('characterSet'));
        System::getContainer()->get('huh.head.tag.base')->setContent(Environment::get('base'));

        // prepare data
        $description = str_replace(["\n", "\r", '"'], [' ', '', ''], $objPage->description);

        if ('' === $titleTag) {
            $firstPage = $this->framework->getAdapter(PageModel::class)->findFirstPublishedByPid($objPage->rootId);

            $title = '{{page::rootPageTitle}}';

            // add pageTitle only if not first page / front page)
            if (null === $firstPage || $firstPage->id !== $objPage->id || isset($_GET['auto_item'])) {
                $title = '{{page::pageTitle}} - '.$title;
            }

            $titleTag = $title;
        }

        // image
        $image = null;

        if (null !== ($rootPage = $this->framework->getAdapter(PageModel::class)->findByPk($objPage->rootId ?: $objPage->id))) {
            if ($rootPage->addHeadDefaultImage && $rootPage->headDefaultImage) {
                if ($imageTmp = System::getContainer()->get('huh.utils.file')->getPathFromUuid($rootPage->headDefaultImage)) {
                    $image = Environment::get('url').'/'.$imageTmp;
                }
            }
        }

        $path = Request::createFromGlobals()->getPathInfo(); // path without query string
        $url = Environment::get('url').$path;

        // if path is id, take absolute url from current page
        if (is_numeric(ltrim($path, '/'))) {
            $url = $objPage->getAbsoluteUrl();
        }

        if (System::getContainer()->get('huh.request')->hasGet('full')) {
            $url = System::getContainer()->get('huh.utils.url')->getCurrentUrl([]);
        }

        // title tag
        System::getContainer()->get('huh.head.tag.title')->setContent($titleTag);

        // default meta data
        System::getContainer()->get('huh.head.tag.meta_language')->setContent(System::getContainer()->get('translator')->getLocale());
        System::getContainer()->get('huh.head.tag.meta_description')->setContent($description);
        System::getContainer()->get('huh.head.tag.meta_robots')->setContent($objPage->robots ?: 'index,follow');

        // default twitter card
        System::getContainer()->get('huh.head.tag.twitter_card')->setContent('summary');
        System::getContainer()->get('huh.head.tag.twitter_title')->setContent($titleTag);
        System::getContainer()->get('huh.head.tag.twitter_description')->setContent($description);

        if ($image) {
            System::getContainer()->get('huh.head.tag.twitter_image')->setContent($image);
        }

        // default open graph data
        System::getContainer()->get('huh.head.tag.og_title')->setContent($titleTag);
        System::getContainer()->get('huh.head.tag.og_description')->setContent($description);
        System::getContainer()->get('huh.head.tag.og_url')->setContent($url);

        if ($image) {
            System::getContainer()->get('huh.head.tag.og_image')->setContent($image);
        }

        // canonical
        System::getContainer()->get('huh.head.tag.link_canonical')->setContent($url);
    }
}
