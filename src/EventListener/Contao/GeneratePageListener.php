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
use HeimrichHannot\HeadBundle\Manager\HtmlHeadTagManager;
use HeimrichHannot\HeadBundle\Manager\TagManager;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * @Hook("generatePage", priority=-10)
 */
class GeneratePageListener implements ServiceSubscriberInterface
{
    private TagManager         $manager;
    private array              $config;
    private ContainerInterface $container;
    private HtmlHeadTagManager $headTagManager;

    public function __construct(ContainerInterface $container, TagManager $manager, array $bundleConfig, HtmlHeadTagManager $headTagManager)
    {
        $this->manager = $manager;
        $this->config = $bundleConfig;
        $this->container = $container;
        $this->headTagManager = $headTagManager;
    }

    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        if ($this->config['use_contao_head'] ?? false) {
            // 4.13+
            if (class_exists(HtmlHeadBag::class) && $this->container->has(ResponseContextAccessor::class)) {
                $contextAccessor = $this->container->get(ResponseContextAccessor::class);

                if ($contextAccessor->getResponseContext()->has(HtmlHeadBag::class)) {
                    /** @var HtmlHeadBag $htmlHeadBag */
                    $htmlHeadBag = $contextAccessor->getResponseContext()->get(HtmlHeadBag::class);

                    if (($tag = $this->manager->getTagInstance('huh.head.tag.title')) && $tag->hasContent()) {
                        $htmlHeadBag->setTitle(StringUtil::stripInsertTags(Controller::replaceInsertTags($tag->getContent())));
                        $this->manager->removeTag('huh.head.tag.title');
                    }

                    if ($tag = $this->headTagManager->getMetaTag('description')) {
                        $htmlHeadBag->setMetaDescription(StringUtil::stripInsertTags(Controller::replaceInsertTags($tag->getAttributes()['content'] ?? '')));
                        $this->headTagManager->removeMetaTag('description');
                    }

                    if ($tag = $this->headTagManager->getMetaTag('robots')) {
                        $htmlHeadBag->setMetaRobots($tag->getAttributes()['content']);
                        $this->headTagManager->removeMetaTag('robots');
                    }

                    if (($tag = $this->manager->getTagInstance('huh.head.tag.link_canonical')) && $tag->hasContent()) {
                        $htmlHeadBag->setCanonicalUri(StringUtil::stripInsertTags(Controller::replaceInsertTags($tag->getContent())));
                        $this->manager->removeTag('huh.head.tag.link_canonical');
                    }
                }
            } else {
                if (($tag = $this->manager->getTagInstance('huh.head.tag.title')) && $tag->hasContent()) {
                    $layout->titleTag = $tag->getContent();
                    $this->manager->removeTag('huh.head.tag.title');
                }

                if ($tag = $this->headTagManager->getMetaTag('description')) {
                    $pageModel->description = $tag->getAttributes()['content'] ?? '';
                    $this->headTagManager->removeMetaTag('description');
                }

                if ($tag = $this->headTagManager->getMetaTag('robots')) {
                    $pageModel->robots = $tag->getAttributes()['content'] ?? '';
                    $this->headTagManager->removeMetaTag('robots');
                }
            }

            if ($tag = $this->headTagManager->getBaseTag()) {
                $pageRegular->Template->base = $tag->getAttributes()['href'];
            }
        }
    }

    public static function getSubscribedServices()
    {
        return [
            '?'.ResponseContextAccessor::class,
        ];
    }
}
