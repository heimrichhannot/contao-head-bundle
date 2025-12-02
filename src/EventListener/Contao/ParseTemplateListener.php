<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\EventListener\Contao;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Template;
use HeimrichHannot\HeadBundle\Helper\LegacyHelper;
use HeimrichHannot\HeadBundle\Manager\HtmlHeadTagManager;
use HeimrichHannot\HeadBundle\Manager\JsonLdManager;
use HeimrichHannot\HeadBundle\Model\PageModel;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Spatie\SchemaOrg\Graph;
use Spatie\SchemaOrg\Schema;

/**
 * @Hook("parseTemplate")
 */
class ParseTemplateListener
{
    private array              $bundleConfig;
    private HtmlHeadTagManager $headTagManager;
    private JsonLdManager $jsonLdManager;
    private Utils $utils;

    public function __construct(array $bundleConfig, HtmlHeadTagManager $headTagManager, JsonLdManager $jsonLdManager, Utils $utils)
    {
        $this->bundleConfig = $bundleConfig;
        $this->headTagManager = $headTagManager;
        $this->jsonLdManager = $jsonLdManager;
        $this->utils = $utils;
    }

    public function __invoke(Template $template): void
    {
        $this->addLegacyMetaMethod($template);
        $this->createBreadcrumbSchema($template);
        $this->addSchemaFromArrayMethodPolyfill($template);
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
                return $this->headTagManager->renderTags(['skip_tags' => $skip]);
            };
        }
    }

    private function createBreadcrumbSchema(Template $template): void
    {
        if (!str_starts_with($template->getName(), 'mod_breadcrumb')) {
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

        $breadcrumb = $this->jsonLdManager->getGraphForSchema(JsonLdManager::SCHEMA_ORG)->breadcrumbList();

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

    /**
     * @todo Remove this method when contao 4.12+ is required
     */
    private function addSchemaFromArrayMethodPolyfill(Template $template): void
    {
        if (method_exists($template, 'addSchemaOrg')) {
            return;
        }

        $jsonLdManager = $this->jsonLdManager;

        $template->addSchemaOrg = function (array $jsonLd) use ($jsonLdManager): void {
            $type = $jsonLdManager->createSchemaOrgTypeFromArray($jsonLd);

            $jsonLdManager
                ->getGraphForSchema(JsonLdManager::SCHEMA_ORG)
                ->set($type, $jsonLd['identifier'] ?? Graph::IDENTIFIER_DEFAULT)
            ;
        };
    }
}
