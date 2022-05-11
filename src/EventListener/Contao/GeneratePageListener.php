<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\EventListener\Contao;

use Contao\Controller;
use Contao\CoreBundle\Routing\ResponseContext\HtmlHeadBag\HtmlHeadBag;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use Contao\StringUtil;
use HeimrichHannot\HeadBundle\Manager\TagManager;

/**
 * @Hook("generatePage", priority=-10)
 */
class GeneratePageListener
{
    private TagManager              $manager;
    private ResponseContextAccessor $contextAccessor;
    private array                   $config;

    public function __construct(TagManager $manager, ResponseContextAccessor $contextAccessor, array $bundleConfig)
    {
        $this->manager = $manager;
        $this->contextAccessor = $contextAccessor;
        $this->config = $bundleConfig;
    }

    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        if (class_exists(HtmlHeadBag::class)) {
            if ($this->contextAccessor->getResponseContext()->has(HtmlHeadBag::class)) {
                /** @var HtmlHeadBag $htmlHeadBag */
                $htmlHeadBag = $this->contextAccessor->getResponseContext()->get(HtmlHeadBag::class);

                if (($tag = $this->manager->getTagInstance('huh.head.tag.title')) && $tag->hasContent()) {
                    $htmlHeadBag->setTitle(StringUtil::stripInsertTags(Controller::replaceInsertTags($tag->getContent())));
                    $this->manager->removeTag('huh.head.tag.title');
                }

                if (($tag = $this->manager->getTagInstance('huh.head.tag.meta_description')) && $tag->hasContent()) {
                    $htmlHeadBag->setMetaDescription(StringUtil::stripInsertTags(Controller::replaceInsertTags($tag->getContent())));
                    $this->manager->removeTag('huh.head.tag.meta_description');
                }

                if (($tag = $this->manager->getTagInstance('huh.head.tag.meta_robots')) && $tag->hasContent()) {
                    $htmlHeadBag->setMetaRobots(StringUtil::stripInsertTags(Controller::replaceInsertTags($tag->getContent())));
                    $this->manager->removeTag('huh.head.tag.meta_robots');
                }

                if (($tag = $this->manager->getTagInstance('huh.head.tag.link_canonical')) && $tag->hasContent()) {
                    $htmlHeadBag->setCanonicalUri(StringUtil::stripInsertTags(Controller::replaceInsertTags($tag->getContent())));
                    $this->manager->removeTag('huh.head.tag.link_canonical');
                }

                if (($tag = $this->manager->getTagInstance('huh.head.tag.base')) && $tag->hasContent()) {
                    $pageRegular->Template->base = StringUtil::stripInsertTags(Controller::replaceInsertTags($tag->getContent()));
                    $this->manager->removeTag('huh.head.tag.base');
                }
            }
        }
    }
}
