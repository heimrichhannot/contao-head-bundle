<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\EventListener\Contao;

use Contao\CoreBundle\Image\ImageFactoryInterface;
use Contao\CoreBundle\ServiceAnnotation\Hook;
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
    private Utils $utils;
    private HtmlHeadTagManager $headTagManager;
    private ImageFactoryInterface $imageFactory;

    public function __construct(
        Utils $utils,
        HtmlHeadTagManager $headTagManager,
        ImageFactoryInterface $imageFactory
    )
    {
        $this->utils = $utils;
        $this->headTagManager = $headTagManager;
        $this->imageFactory = $imageFactory;
    }

    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        if (!$this->utils->container()->isFrontend()) {
            return;
        }

        $this->setPageFallbackImage($pageModel);
        $this->setTwitterTags($pageModel);
    }

    /**
     * @param \HeimrichHannot\HeadBundle\Model\PageModel $pageModel
     * @throws \Exception
     */
    private function setPageFallbackImage(PageModel $pageModel): void
    {
        $metaImageTag = $this->headTagManager->getMetaTag('og:image');
        $twitterImageTag = $this->headTagManager->getMetaTag('twitter:image');

        if ($metaImageTag && $twitterImageTag) {
            return;
        }

        $imagePath = $this->pageImage($pageModel)
            ?: $this->pageImage($this->utils->request()->getCurrentRootPageModel($pageModel));

        if (!$imagePath) {
            return;
        }

        $baseUrl = $this->utils->request()->getBaseUrl(['pageModel' => $pageModel]);

        if (!$metaImageTag) {
            $metaImagePath = $this->imageFactory->create($imagePath, [1200, 630, 'proportional'])->getPath();
            $this->headTagManager->addMetaTag(new PropertyMetaTag('og:image', $baseUrl . \DIRECTORY_SEPARATOR . $metaImagePath));
        }

        if (!$twitterImageTag) {
            $twitterImagePath = $this->imageFactory->create($imagePath, [1024, 512, 'proportional'])->getPath();
            $this->headTagManager->addMetaTag(new MetaTag('twitter:image', $baseUrl . \DIRECTORY_SEPARATOR . $twitterImagePath));
        }
    }

    /**
     * @param \HeimrichHannot\HeadBundle\Model\PageModel|null $pageModel
     * @return string|null
     */
    protected function pageImage(?PageModel $pageModel): ?string
    {
        if (null === $pageModel) {
            return null;
        }
        if (!$pageModel->addHeadDefaultImage) {
            return null;
        }
        if (!$pageModel->headDefaultImage) {
            return null;
        }

        return $this->utils->file()->getPathFromUuid($pageModel->headDefaultImage);
    }

    /**
     * @param \HeimrichHannot\HeadBundle\Model\PageModel $pageModel
     */
    private function setTwitterTags(PageModel $pageModel): void
    {
        /** @var \HeimrichHannot\HeadBundle\Model\PageModel $rootPageModel */
        $rootPageModel = $this->utils->request()->getCurrentRootPageModel($pageModel);
        if (!$rootPageModel || !$rootPageModel->twitterSite) {
            return;
        }

        $this->headTagManager->addMetaTag(
            new MetaTag('twitter:site', (str_starts_with($pageModel->twitterSite, '@') ? '@' : '') . $rootPageModel->twitterSite)
        );
    }
}
