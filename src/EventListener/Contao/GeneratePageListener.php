<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\EventListener\Contao;

use Contao\CoreBundle\InsertTag\InsertTagParser;
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
use HeimrichHannot\HeadBundle\Helper\TagHelper;
use HeimrichHannot\HeadBundle\Manager\HtmlHeadTagManager;
use HeimrichHannot\HeadBundle\Manager\JsonLdManager;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Psr\Container\ContainerInterface;
use Spatie\SchemaOrg\BaseType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * @Hook("generatePage", priority=-10)
 */
class GeneratePageListener implements ServiceSubscriberInterface
{
    private array              $config;
    private ContainerInterface $container;
    private HtmlHeadTagManager $headTagManager;
    private RequestStack       $requestStack;
    private Utils              $utils;
    private TagHelper          $tagHelper;
    private JsonLdManager      $jsonLdManager;
    private InsertTagParser    $insertTagParser;

    public function __construct(
        ContainerInterface $container,
        array $bundleConfig,
        HtmlHeadTagManager $headTagManager,
        RequestStack $requestStack,
        Utils $utils,
        TagHelper $tagHelper,
        JsonLdManager $jsonLdManager,
        InsertTagParser $insertTagParser
    ) {
        $this->config = $bundleConfig;
        $this->container = $container;
        $this->headTagManager = $headTagManager;
        $this->requestStack = $requestStack;
        $this->utils = $utils;
        $this->tagHelper = $tagHelper;
        $this->jsonLdManager = $jsonLdManager;
        $this->insertTagParser = $insertTagParser;
    }

    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        if ($this->config['use_contao_head'] ?? false) {
            $this->setContaoHead($layout, $pageModel, $pageRegular);
            $title = $pageModel->pageTitle ?: $pageModel->title;
            $description = $pageModel->description;
        } else {
            $this->setHeadTagsFromContao($pageRegular, $pageModel);
            $title = ($titleTag = $this->headTagManager->getTitleTag()) ? $titleTag->getTitle() : '';
            $description = ($descriptionTag = $this->headTagManager->getMetaTag('description')) ? $descriptionTag->getContent() : '';
        }

        if (empty($title)) {
            $title = $this->insertTagParser->replace('{{page::pageTitle}}');
        }

        $this->prepareJsonLdContent($pageModel, $title);
        $this->setOpenGraphTags($title ?? '', $description ?? '');
        $this->setTwitterTags();
    }

    public static function getSubscribedServices(): array
    {
        return [
            '?'.ResponseContextAccessor::class,
        ];
    }

    /**
     * Set contao head tags from head bundle tags (use contao template variables instead of head bundle output where possible).
     *
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

        if (($tag = $this->headTagManager->getTitleTag()) && $tag->getTitle()) {
            $pageModel->pageTitle = $tag->getTitle();

            if ($htmlHeadBag) {
                $htmlHeadBag->setTitle($tag->getTitle());
            }
            $this->headTagManager->setTitleTag(null);
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
        if ($htmlHeadBag && ($tag = $this->headTagManager->getCanonical())) {
            $pageModel->enableCanonical = true;
            $htmlHeadBag->setCanonicalUri($tag->generate());
            $this->headTagManager->setCanonical(null);
        }
    }

    /**
     * Set head tags from contao setting/ variables (use head bundle output instead of contao template variables).
     */
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
        if (!($tag = $this->headTagManager->getTitleTag()) || !$tag->getTitle()) {
            $titleFormat = str_replace('{{page::pageTitle}}', '%s', $this->tagHelper->getContaoTitleTag($pageModel));
            $title = $this->insertTagParser->replace('{{page::pageTitle}}');

            $this->headTagManager->setTitleTag($this->headTagManager->inputEncodedToPlainText($title), $titleFormat);
        }

        // Description
        if (!$this->headTagManager->getMetaTag('description')) {
            $description = $pageModel->description;

            if ($htmlHeadBag && !empty($htmlHeadBag->getMetaDescription())) {
                $description = $htmlHeadBag->getMetaDescription();
            }
            $this->headTagManager->addMetaTag(new MetaTag('description', $this->tagHelper->prepareDescription($description ?? '')));
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
        if (!$this->headTagManager->getCanonical()) {
            if ($htmlHeadBag && $pageModel->enableCanonical) {
                $this->headTagManager->setCanonical(
                    htmlspecialchars($htmlHeadBag->getCanonicalUriForRequest($this->requestStack->getCurrentRequest()))
                );
            }
        }
    }

    /**
     * @param string|null $description
     */
    protected function setOpenGraphTags(string $title, string $description): void
    {
        if (!$this->headTagManager->getMetaTag('og:title')) {
            $this->headTagManager->addMetaTag(new PropertyMetaTag('og:title', $title));
        }

        if (!empty($description)) {
            if (!$this->headTagManager->getMetaTag('og:description')) {
                $this->headTagManager->addMetaTag(new PropertyMetaTag('og:description', $this->tagHelper->prepareDescription($description)));
            }
        }

        if (!$this->headTagManager->getMetaTag('og:url')) {
            $request = $this->requestStack->getCurrentRequest();

            if ($headTagBag = $this->getHtmlHeadBag()) {
                $url = $headTagBag->getCanonicalUriForRequest($request);
            } else {
                $url = Request::create(
                    $request->getSchemeAndHttpHost().$request->getBaseUrl().$request->getPathInfo(),
                    $request->getMethod(),
                )->getUri();
            }
            $this->headTagManager->addMetaTag(new PropertyMetaTag('og:url', $url));
        }
    }

    protected function setTwitterTags(): void
    {
        if (!$this->headTagManager->getMetaTag('twitter:card')) {
            $this->headTagManager->addMetaTag(new MetaTag('twitter:card', 'summary'));
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

    private function prepareJsonLdContent(PageModel $pageModel, string $title): void
    {
        $rootPageModel = $this->utils->request()->getCurrentRootPageModel($pageModel);

        if (!$rootPageModel) {
            return;
        }

        if ($rootPageModel->headAddOrganisationSchema) {
            $organisation = $this->jsonLdManager->getGraphForSchema(JsonLdManager::SCHEMA_ORG)->organization();

            if ($rootPageModel->headOrganisationName) {
                $organisation->name($rootPageModel->headOrganisationName);
            }

            if ($rootPageModel->headOrganisationWebsite) {
                $organisation->url($rootPageModel->headOrganisationWebsite);
            }

            if ($rootPageModel->headOrganisationLogo) {
                $path = $this->utils->file()->getPathFromUuid($rootPageModel->headOrganisationLogo);

                if (null !== $path) {
                    $organisation->logo($path);
                }
            }
        }

        if ($rootPageModel->headAddWebSiteSchema) {
            $website = $this->jsonLdManager->getGraphForSchema(JsonLdManager::SCHEMA_ORG)->website();
            $this->setPropertyIfNotSet($website, 'name', $this->insertTagParser->replace('{{page::mainPageTitle}}'));
            $this->setPropertyIfNotSet($website, 'url', $this->utils->request()->getBaseUrl(['pageModel' => $pageModel]));
        }

        if ($rootPageModel->headAddWebPageSchema && !$this->utils->request()->isIndexPage($pageModel)) {
            $webpage = $this->jsonLdManager->getGraphForSchema(JsonLdManager::SCHEMA_ORG)->webpage();
            $this->setPropertyIfNotSet($webpage, 'name', $title);

            if ($pageModel->description) {
                $this->setPropertyIfNotSet($webpage, 'description', $pageModel->description);
            }
        }
    }

    private function setPropertyIfNotSet(BaseType $type, string $property, string $value): void
    {
        if (!$type->getProperty($property)) {
            $type->setProperty($property, $value);
        }
    }
}
