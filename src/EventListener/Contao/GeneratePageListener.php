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
use HeimrichHannot\HeadBundle\HeadTag\BaseTag;
use HeimrichHannot\HeadBundle\HeadTag\Meta\CharsetMetaTag;
use HeimrichHannot\HeadBundle\HeadTag\Meta\PropertyMetaTag;
use HeimrichHannot\HeadBundle\HeadTag\MetaTag;
use HeimrichHannot\HeadBundle\Helper\MetaTagHelper;
use HeimrichHannot\HeadBundle\Manager\HtmlHeadTagManager;
use HeimrichHannot\HeadBundle\Manager\TagManager;
use HeimrichHannot\HeadBundle\Tag\Link\LinkCanonical;
use HeimrichHannot\HeadBundle\Tag\Misc\Title;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * @Hook("generatePage", priority=-10)
 */
class GeneratePageListener implements ServiceSubscriberInterface
{
    private TagManager         $legacyTagManager;
    private array              $config;
    private ContainerInterface $container;
    private HtmlHeadTagManager $headTagManager;
    private RequestStack       $requestStack;
    private Utils              $utils;

    public function __construct(ContainerInterface $container, TagManager $manager, array $bundleConfig, HtmlHeadTagManager $headTagManager, RequestStack $requestStack, Utils $utils)
    {
        $this->legacyTagManager = $manager;
        $this->config = $bundleConfig;
        $this->container = $container;
        $this->headTagManager = $headTagManager;
        $this->requestStack = $requestStack;
        $this->utils = $utils;
    }

    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        if ($this->config['use_contao_head'] ?? false) {
            $this->setContaoHead($layout, $pageModel, $pageRegular);
            $title = $layout->titleTag;
            $description = $pageModel->description;
        } else {
            $this->setHeadTagsFromContao($pageRegular, $pageModel);
            $title = ($tag = $this->legacyTagManager->getTagInstance('huh.head.tag.og_title')) ? $tag->getContent() : '';
            $description = $this->headTagManager->getMetaTag('description')->getContent();
        }

        $this->setOpenGraphTag($title, $description);
        $this->setTwitterTag();
    }

    public static function getSubscribedServices()
    {
        return [
            '?'.ResponseContextAccessor::class,
        ];
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function setContaoHead(LayoutModel $layout, PageModel $pageModel, PageRegular $pageRegular): void
    {
        $htmlHeadBag = $this->getHtmlHeadBag();

        if ($tag = $this->headTagManager->getMetaTag('charset')) {
            $pageRegular->Template->charset = $tag->getAttributes()['content'];
            $this->headTagManager->removeMetaTag('charset');
        }

        if ($tag = $this->headTagManager->getBaseTag()) {
            $pageRegular->Template->base = $tag->getAttributes()['href'];
            $this->headTagManager->setBaseTag(null);
        }

        if (($tag = $this->legacyTagManager->getTagInstance('huh.head.tag.title')) && $tag->hasContent()) {
            $layout->titleTag = $tag->getContent();

            if ($htmlHeadBag) {
                $htmlHeadBag->setTitle($tag->getContent());
            }
            $this->legacyTagManager->removeTag('huh.head.tag.title');
        }

        if ($tag = $this->headTagManager->getMetaTag('description')) {
            $pageModel->description = $tag->getAttributes()['content'] ?? '';

            if ($htmlHeadBag) {
                $htmlHeadBag->setMetaDescription($tag->getAttributes()['content'] ?? '');
            }
            $this->headTagManager->removeMetaTag('description');
        }

        if ($tag = $this->headTagManager->getMetaTag('robots')) {
            $pageModel->robots = $tag->getAttributes()['content'] ?? '';

            if ($htmlHeadBag) {
                $htmlHeadBag->setMetaRobots($tag->getAttributes()['content']);
            }
            $this->headTagManager->removeMetaTag('robots');
        }

        // Canonical Link
        if (($htmlHeadBag && $tag = $this->legacyTagManager->getTagInstance('huh.head.tag.link_canonical')) && $tag->hasContent()) {
            $pageModel->enableCanonical = true;
            $htmlHeadBag->setCanonicalUri($tag->getContent());
        }
    }

    protected function setHeadTagsFromContao(PageRegular $pageRegular, PageModel $pageModel): void
    {
        $htmlHeadBag = $this->getHtmlHeadBag();

        // Charset
        if (!$this->headTagManager->getMetaTag('charset')) {
            $this->headTagManager->addMetaTag(new CharsetMetaTag($pageRegular->Template->charset));
        }

        // Base tag
        if (!$this->headTagManager->getBaseTag()) {
            $this->headTagManager->setBaseTag(new BaseTag($pageRegular->Template->base));
        }

        // Title
        if (!($tag = $this->legacyTagManager->getTagInstance('huh.head.tag.title')) || !$tag->hasContent()) {
            $titleTag = $objLayout->titleTag ?? '';

            if ($htmlHeadBag && !empty($htmlHeadBag->getTitle())) {
                $titleTag = $htmlHeadBag->getTitle();
            }

            if (empty($titleTag)) {
                $titleTag = '{{page::pageTitle}} - {{page::rootPageTitle}}';

                if ($this->utils->request()->isIndexPage($pageModel)) {
                    $titleTag = '{{page::rootPageTitle}}';
                }
            }

            if (!$tag) {
                $tag = new Title($this->legacyTagManager);
                $this->legacyTagManager->registerTag($tag);
            }
            $tag->setContent(strip_tags(Controller::replaceInsertTags($titleTag)));
        }

        // Description
        if (!$this->headTagManager->getMetaTag('description')) {
            $description = $pageModel->description;

            if ($htmlHeadBag && !empty($htmlHeadBag->getMetaDescription())) {
                $description = $htmlHeadBag->getMetaDescription();
            }
            $description = str_replace(["\n", "\r", '"'], [' ', '', ''], strip_tags(Controller::replaceInsertTags($description)) ?? '');
            $this->headTagManager->addMetaTag(new MetaTag('description', MetaTagHelper::prepareDescription($description)));
        }

        // Robots
        if (!$this->headTagManager->getMetaTag('robots')) {
            $robots = $pageModel->robots ?: 'index,follow';

            if ($htmlHeadBag && !empty($htmlHeadBag->getMetaRobots())) {
                $robots = $htmlHeadBag->getMetaRobots();
            }
            $this->headTagManager->addMetaTag(new MetaTag('robots', $robots));
        }

        // Canonical Link
        if (!($tag = $this->legacyTagManager->getTagInstance('huh.head.tag.link_canonical')) || !$tag->hasContent()) {
            if ($htmlHeadBag && $pageModel->enableCanonical) {
                if (!$tag) {
                    $tag = new LinkCanonical($this->legacyTagManager);
                    $this->legacyTagManager->registerTag($tag);
                }
                $tag->setContent(htmlspecialchars($htmlHeadBag->getCanonicalUriForRequest($this->requestStack->getCurrentRequest())));
            }
        }
    }

    /**
     * @return HtmlHeadBag|null
     */
    private function getHtmlHeadBag(): ?object
    {
        if (class_exists(HtmlHeadBag::class) && $this->container->has(ResponseContextAccessor::class)) {
            $contextAccessor = $this->container->get(ResponseContextAccessor::class);

            if ($contextAccessor->getResponseContext()->has(HtmlHeadBag::class)) {
                /* @var HtmlHeadBag $htmlHeadBag */
                return $contextAccessor->getResponseContext()->get(HtmlHeadBag::class);
            }
        }

        return null;
    }

    /**
     * @param string $title
     * @param string|null $description
     * @return void
     */
    protected function setOpenGraphTag(string $title, string $description): void
    {
        if (!$this->headTagManager->getMetaTag('og_title')) {
            $this->headTagManager->addMetaTag(new PropertyMetaTag('og:title', $title));
        }

        if (!$this->headTagManager->getMetaTag('og_description')) {
            $this->headTagManager->addMetaTag(new PropertyMetaTag('og:description', MetaTagHelper::prepareDescription($description)));
        }

        if (!$this->headTagManager->getMetaTag('og_url')) {
            $request = $this->requestStack->getCurrentRequest();

            if ($headTagBag = $this->getHtmlHeadBag()) {
                $url = $headTagBag->getCanonicalUriForRequest($request);
            } else {
                $url = Request::create(
                    $request->getSchemeAndHttpHost() . $request->getBaseUrl() . $request->getPathInfo(),
                    $request->getMethod(),
                )->getUri();
            }
            $this->headTagManager->addMetaTag(new PropertyMetaTag('og:url', $url));
        }


    }

    /**
     * @return void
     */
    protected function setTwitterTag(): void
    {
        if (!$this->headTagManager->getMetaTag('twitter_card')) {
            $this->headTagManager->addMetaTag(new MetaTag('twitter:card', 'summary'));
        }
    }
}
