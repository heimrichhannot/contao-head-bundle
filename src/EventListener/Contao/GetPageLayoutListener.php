<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\EventListener\Contao;

use Contao\CoreBundle\Routing\ResponseContext\JsonLd\JsonLdManager;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Image;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use HeimrichHannot\HeadBundle\Manager\TagManager;
use HeimrichHannot\HeadBundle\Tag\Meta\OG\OGImage;
use HeimrichHannot\HeadBundle\Tag\Meta\Twitter\TwitterImage;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * @Hook("getPageLayout", priority=-10)
 */
class GetPageLayoutListener implements ServiceSubscriberInterface
{
    private ContainerInterface $container;
    private Utils              $utils;
    private TagManager         $tagManager;

    public function __construct(ContainerInterface $container, Utils $utils, TagManager $tagManager)
    {
        $this->container = $container;
        $this->utils = $utils;
        $this->tagManager = $tagManager;
    }

    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        if (!$this->utils->container()->isFrontend()) {
            return;
        }

        $this->setPageFallbackImage($pageModel);

//        if ($this->container->has(ResponseContextAccessor::class) && $this->container->get(ResponseContextAccessor::class)->getResponseContext()->has(JsonLdManager::class)) {
//
//            /** @var JsonLdManager $schemaManager */
//            $schemaManager = $this->container->get(ResponseContextAccessor::class)->getResponseContext()->get(JsonLdManager::class);
//            $graph = $schemaManager->getGraphForSchema(JsonLdManager::SCHEMA_ORG);
//            $graph->webPage()->image($baseUrl.DIRECTORY_SEPARATOR.$imagePath);
//            $this->tagManager->removeTag('huh.head.tag.og_image');
//        }
    }

    public static function getSubscribedServices()
    {
        return [
            '?'.ResponseContextAccessor::class,
            'huh.head.tag.og_image' => '?'.OGImage::class,
        ];
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
