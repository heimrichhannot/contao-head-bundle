<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\HeadBundle\EventListener;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use HeimrichHannot\Haste\Util\Url;
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
     * @param \PageModel   $page
     * @param \LayoutModel $layout
     * @param \PageRegular $pageRegular
     */
    public function generatePage(\PageModel $page, \LayoutModel $layout, \PageRegular $pageRegular)
    {
        $pageRegular->Template->meta = implode("\n", $this->tagManager->getTags());
    }

    /**
     * Modify the page layout.
     *
     * @param \PageModel   $page
     * @param \LayoutModel $layout
     * @param \PageRegular $pageRegular
     */
    public function getPageLayout(\PageModel $page, \LayoutModel $layout, \PageRegular $pageRegular)
    {
        /*
         * @var $objPage \Contao\PageModel
         */
        global $objPage;

        \System::getContainer()->get('huh.head.tag.meta_charset')->setContent(\Config::get('characterSet'));
        \System::getContainer()->get('huh.head.tag.base')->setContent(\Environment::get('base'));

        // Fall back to the default title tag
        if ('' === $layout->titleTag) {
            $objFirstPage = \PageModel::findFirstPublishedByPid($objPage->rootId);
            $strTitle = '{{page::rootPageTitle}}';

            // add pageTitle only if not first page / front page)
            if (null === $objFirstPage || $objFirstPage->id !== $objPage->id) {
                $strTitle = '{{page::pageTitle}} - '.$strTitle;
            }

            $layout->titleTag = $strTitle;
        }

        \System::getContainer()->get('huh.head.tag.title')->setContent($layout->titleTag);

        \System::getContainer()->get('huh.head.tag.meta_language')->setContent(\System::getContainer()->get('translator')->getLocale());
        \System::getContainer()->get('huh.head.tag.meta_description')->setContent(str_replace(["\n", "\r", '"'], [' ', '', ''], $objPage->description));
        \System::getContainer()->get('huh.head.tag.meta_robots')->setContent($objPage->robots ?: 'index,follow');

        $path = Request::createFromGlobals()->getPathInfo(); // path without query string
        $url = \Contao\Environment::get('url').$path;

        // if path is id, take absolute url from current page
        if (is_numeric(ltrim($path, '/'))) {
            $url = $objPage->getAbsoluteUrl();
        }

        \System::getContainer()->get('huh.head.tag.link_canonical')->setContent($url);
    }
}
