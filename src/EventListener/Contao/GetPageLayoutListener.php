<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\EventListener\Contao;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Image;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use HeimrichHannot\HeadBundle\Manager\TagManager;
use HeimrichHannot\HeadBundle\Tag\Meta\OG\OGImage;
use HeimrichHannot\HeadBundle\Tag\Meta\Twitter\TwitterImage;
use HeimrichHannot\UtilsBundle\Util\Utils;

/**
 * @Hook("getPageLayout", priority=-10)
 */
class GetPageLayoutListener
{
    private Utils              $utils;
    private TagManager         $tagManager;

    public function __construct(Utils $utils, TagManager $tagManager)
    {
        $this->utils = $utils;
        $this->tagManager = $tagManager;
    }

    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        if (!$this->utils->container()->isFrontend()) {
            return;
        }

        $this->setPageFallbackImage($pageModel);
    }

    /**
     * @throws \Exception
     */
    private function setPageFallbackImage(PageModel $pageModel): void
    {
        if ($this->tagManager->hasTag('huh.head.tag.og_image')) {
            $metaImageTag = $this->tagManager->getTagInstance('huh.head.tag.og_image');

            if ($metaImageTag->hasContent()) {
                $metaImageTag = null;
            }
        } else {
            $metaImageTag = new OGImage($this->tagManager);
            $this->tagManager->registerTag($metaImageTag);
        }

        if ($this->tagManager->hasTag('huh.head.tag.twitter_image')) {
            $twitterImageTag = $this->tagManager->getTagInstance('huh.head.tag.twitter_image');

            if ($twitterImageTag->hasContent()) {
                $twitterImageTag = null;
            }
        } else {
            $twitterImageTag = new TwitterImage($this->tagManager);
            $this->tagManager->registerTag($twitterImageTag);
        }

        if ($metaImageTag || $twitterImageTag) {
            $imagePath = null;

            if ($pageModel->addHeadDefaultImage && $pageModel->headDefaultImage) {
                $imagePath = $this->utils->file()->getPathFromUuid($pageModel->headDefaultImage);
            } elseif (($rootPageModel = $this->utils->request()->getCurrentRootPageModel($pageModel))
                && $rootPageModel->addHeadDefaultImage && $rootPageModel->headDefaultImage) {
                $imagePath = $this->utils->file()->getPathFromUuid($rootPageModel->headDefaultImage);
            }

            if (!$imagePath) {
                return;
            }
        } else {
            return;
        }

        $baseUrl = $this->utils->request()->getBaseUrl(['pageModel' => $pageModel]);

        if ($metaImageTag) {
            $metaImagePath = Image::get($imagePath, 1200, 630, 'proportional');
            $metaImageTag->setContent($baseUrl.\DIRECTORY_SEPARATOR.$metaImagePath);
        }

        if ($twitterImageTag) {
            $twitterImagePath = Image::get($imagePath, 1024, 512, 'proportional');
            $twitterImageTag->setContent($baseUrl.\DIRECTORY_SEPARATOR.$twitterImagePath);
        }
    }
}
