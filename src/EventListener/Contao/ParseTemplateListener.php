<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\EventListener\Contao;

use Contao\CoreBundle\Routing\ResponseContext\JsonLd\JsonLdManager;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Template;
use HeimrichHannot\HeadBundle\Helper\LegacyHelper;
use HeimrichHannot\HeadBundle\Manager\HtmlHeadTagManager;
use HeimrichHannot\HeadBundle\Model\PageModel;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Psr\Container\ContainerInterface;
use Spatie\SchemaOrg\Schema;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * @Hook("parseTemplate")
 */
class ParseTemplateListener implements ServiceSubscriberInterface
{
    private array $bundleConfig;
    private HtmlHeadTagManager $headTagManager;
    private ContainerInterface $container;
    private Utils $utils;

    public function __construct(array $bundleConfig, HtmlHeadTagManager $headTagManager, ContainerInterface $container, Utils $utils)
    {
        $this->bundleConfig = $bundleConfig;
        $this->headTagManager = $headTagManager;
        $this->container = $container;
        $this->utils = $utils;
    }

    public function __invoke(Template $template): void
    {
        $this->addLegacyMetaMethod($template);
        $this->createBreadcrumbSchema($template);
    }

    public static function getSubscribedServices(): array
    {
        return [
            '?' . ResponseContextAccessor::class,
        ];
    }

    protected function addLegacyMetaMethod(Template $template): void
    {
        if (!str_starts_with($template->getName(), 'fe_page')) {
            return;
        }

        if (!($this->bundleConfig['use_contao_variables'] ?? false)) {
            $template->meta = function (array $skip = []) {
                foreach ($skip as &$tag) {
                    $tag = LegacyHelper::mapServiceToTag($tag, $tag);
                }

                return $this->headTagManager->renderTags([
                    'skip_tags' => $skip,
                ]);
            };
        }
    }

    private function createBreadcrumbSchema(Template $template): void
    {
        if (!str_starts_with($template->getName(), 'mod_breadcrumb')) {
            return;
        }

        $jsonLdManager = $this->getJsonLdManager();

        if (!$jsonLdManager) {
            return;
        }

        /** @var PageModel|null $rootPageModel */
        $rootPageModel = $this->utils->request()->getCurrentRootPageModel();
        if (!$rootPageModel || !$rootPageModel->headAddBreadcrumbSchema) {
            return;
        }

        if ($this->utils->request()->isIndexPage()) {
            return;
        }

        if (!$template->items || !\is_array($items = $template->items)) {
            return;
        }

        $breadcrumb = $jsonLdManager->getGraphForSchema(JsonLdManager::SCHEMA_ORG)->breadcrumbList();

        if (!$breadcrumb->getProperty('itemListElement')) {
            $listItems = [];
            $position = 0;

            foreach ($items as $item) {
                $listItem = Schema::listItem();
                $listItem->position(++$position);
                $listItem->name($item['title']);
                $listItem->item($item['href']);
                $listItems[] = $listItem;
            }
            $breadcrumb->itemListElement($listItems);
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
}
