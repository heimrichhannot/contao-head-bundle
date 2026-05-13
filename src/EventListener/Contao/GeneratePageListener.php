<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\EventListener\Contao;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\CoreBundle\Routing\ResponseContext\HtmlHeadBag\HtmlHeadBag;
use Contao\CoreBundle\Routing\ResponseContext\JsonLd\JsonLdManager;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use HeimrichHannot\HeadBundle\HeadTag\BaseTag;
use HeimrichHannot\HeadBundle\HeadTag\Meta\CharsetMetaTag;
use HeimrichHannot\HeadBundle\HeadTag\Meta\PropertyMetaTag;
use HeimrichHannot\HeadBundle\HeadTag\MetaTag;
use HeimrichHannot\HeadBundle\Helper\TagHelper;
use HeimrichHannot\HeadBundle\Manager\HtmlHeadTagManager;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spatie\SchemaOrg\BaseType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

#[AsHook('generatePage', priority: -10)]
class GeneratePageListener implements ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        private array $config,
        private readonly HtmlHeadTagManager $headTagManager,
        private readonly RequestStack $requestStack,
        private readonly Utils $utils,
        private readonly TagHelper $tagHelper,
        private readonly InsertTagParser $insertTagParser,
        private readonly ResponseContextAccessor $responseContextAccessor,
    ) {
    }

    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        if ($this->config['use_contao_head'] ?? false) {
            $this->setContaoHead($layout, $pageModel, $pageRegular);
            $description = $pageModel->description;
        } else {
            $this->setHeadTagsFromContao($pageRegular, $pageModel);
            $description = ($descriptionTag = $this->headTagManager->getMetaTag('description')) ? $descriptionTag->getContent() : '';
        }

        $headBag = $this->getHtmlHeadBag();
        $title = $headBag?->getTitle() ?: '';

        if ('' === $title) {
            $title = $this->insertTagParser->replace('{{page::pageTitle}}');
        }

        $this->prepareJsonLdContent($pageModel);
        $this->setOpenGraphTags($title, $description ?? '');
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
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
            $htmlHeadBag->setCanonicalUri($tag->getAttributes()['href']);
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

    private function getHtmlHeadBag(): ?HtmlHeadBag
    {
        if ($this->responseContextAccessor->getResponseContext()->has(HtmlHeadBag::class)) {
            return $this->responseContextAccessor->getResponseContext()->get(HtmlHeadBag::class);
        }

        return null;
    }

    private function prepareJsonLdContent(PageModel $pageModel): void
    {
        $jsonLdManager = $this->getJsonLdManager();

        if (!$jsonLdManager) {
            return;
        }

        /** @var \HeimrichHannot\HeadBundle\Model\PageModel $rootPageModel */
        $rootPageModel = $this->utils->request()->getCurrentRootPageModel($pageModel);

        if (!$rootPageModel) {
            return;
        }

        if ($rootPageModel->headAddOrganisationSchema) {
            $organisation = $jsonLdManager->getGraphForSchema(JsonLdManager::SCHEMA_ORG)->organization();

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
            $website = $jsonLdManager->getGraphForSchema(JsonLdManager::SCHEMA_ORG)->webSite();
            $this->setPropertyIfNotSet($website, 'name', $this->insertTagParser->replace('{{page::mainPageTitle}}'));
            $this->setPropertyIfNotSet($website, 'url', $this->utils->request()->getBaseUrl([
                'pageModel' => $pageModel,
            ]));
        }
    }

    private function getJsonLdManager(): ?JsonLdManager
    {
        if (!$this->container->has(ResponseContextAccessor::class)) {
            return null;
        }

        $responseContext = $this->container->get(ResponseContextAccessor::class)->getResponseContext();

        if (!$responseContext->has(JsonLdManager::class)) {
            return null;
        }

        return $responseContext->get(JsonLdManager::class);
    }

    private function setPropertyIfNotSet(BaseType $type, string $property, string $value): void
    {
        if (!$type->getProperty($property)) {
            $type->setProperty($property, $value);
        }
    }
}
