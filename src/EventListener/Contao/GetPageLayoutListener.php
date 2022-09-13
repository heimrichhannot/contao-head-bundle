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
use HeimrichHannot\HeadBundle\HeadTag\Meta\PropertyMetaTag;
use HeimrichHannot\HeadBundle\HeadTag\MetaTag;
use HeimrichHannot\HeadBundle\Manager\HtmlHeadTagManager;
use HeimrichHannot\UtilsBundle\Util\Utils;

/**
 * @Hook("getPageLayout", priority=-10)
 */
class GetPageLayoutListener
{
    private Utils              $utils;
    private HtmlHeadTagManager $headTagManager;

    public function __construct(Utils $utils, HtmlHeadTagManager $headTagManager)
    {
        $this->utils = $utils;
        $this->headTagManager = $headTagManager;
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
        $metaImageTag = $this->headTagManager->getMetaTag('og_image');
        $twitterImageTag = $this->headTagManager->getMetaTag('twitter_image');

        if (!$metaImageTag || !$twitterImageTag) {
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

        if (!$metaImageTag) {
            $metaImagePath = Image::get($imagePath, 1200, 630, 'proportional');
            $this->headTagManager->addMetaTag(new PropertyMetaTag('og:image', $baseUrl.\DIRECTORY_SEPARATOR.$metaImagePath));
        }

        if (!$twitterImageTag) {
            $twitterImagePath = Image::get($imagePath, 1024, 512, 'proportional');
            $this->headTagManager->addMetaTag(new MetaTag('twitter:image', $baseUrl.\DIRECTORY_SEPARATOR.$twitterImagePath));
        }
    }
}
