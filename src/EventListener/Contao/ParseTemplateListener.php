<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\EventListener\Contao;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Template;
use HeimrichHannot\HeadBundle\Manager\HtmlHeadTagManager;
use HeimrichHannot\HeadBundle\Manager\JsonLdManager;
use HeimrichHannot\UtilsBundle\Util\Utils;
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
        $this->preparePageTemplate($template);
        $this->breadcrump($template);
    }

    protected function preparePageTemplate(Template $template): void
    {
        if (!str_starts_with($template->getName(), 'fe_page')) {
            return;
        }

        if (!($this->bundleConfig['use_contao_variables'] ?? false)) {
            $template->meta = function (array $skip = []) {
                return $this->headTagManager->renderTags(['skip_tags' => $skip]);
            };
        }
    }

    private function breadcrump(Template $template)
    {
        if (!str_starts_with($template->getName(), 'mod_breadcrumb')) {
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
}
