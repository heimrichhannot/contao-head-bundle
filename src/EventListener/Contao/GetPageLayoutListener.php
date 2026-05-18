<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\EventListener\Contao;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Image\ImageFactoryInterface;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use HeimrichHannot\HeadBundle\HeadTag\Meta\PropertyMetaTag;
use HeimrichHannot\HeadBundle\HeadTag\MetaTag;
use HeimrichHannot\HeadBundle\Manager\HtmlHeadTagManager;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsHook('getPageLayout', priority: -10)]
readonly class GetPageLayoutListener
{
    public function __construct(
        private Utils                 $utils,
        private HtmlHeadTagManager    $headTagManager,
        private ImageFactoryInterface $imageFactory,
        private ParameterBagInterface $parameterBag,
    ) {
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
     *
     * @throws \Exception
     */
    private function setPageFallbackImage(PageModel $pageModel): void
    {
        $metaImageTag = $this->headTagManager->getMetaTag('og:image');

        if ($metaImageTag) {
            return;
        }

        $path = $this->pageImage($pageModel);
        if (!$path) {
            $root = $this->utils->request()->getCurrentRootPageModel($pageModel);
            if ($pageModel->id === $root->id) {
                return;
            }
            $path = $this->pageImage($root);
        }

        if (!$path) {
            return;
        }

        $baseUrl = $this->utils->request()->getBaseUrl([
            'pageModel' => $pageModel,
        ]);

        $metaImagePath = $this->imageFactory->create($path, [1200, 630, 'proportional'])->getUrl($this->parameterBag->get('kernel.project_dir'));
        $this->headTagManager->addMetaTag(new PropertyMetaTag('og:image', $baseUrl.'/'.$metaImagePath));
    }

    /**
     * @param \HeimrichHannot\HeadBundle\Model\PageModel $pageModel
     */
    private function pageImage(PageModel $pageModel): ?string
    {
        if (!$pageModel->addHeadDefaultImage) {
            return null;
        }
        if (!$pageModel->headDefaultImage) {
            return null;
        }

        return $this->utils->file()->getPathFromUuid($pageModel->headDefaultImage, [
            'checkIfExists' => true,
            'absolutePath' => true,
        ]);
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
            new MetaTag('twitter:site', (str_starts_with($pageModel->twitterSite, '@') ? '@' : '').$rootPageModel->twitterSite)
        );
    }
}
