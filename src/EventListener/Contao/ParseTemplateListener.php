<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\EventListener\Contao;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Template;
use HeimrichHannot\HeadBundle\Manager\TagManager;

/**
 * @Hook("parseTemplate")
 */
class ParseTemplateListener
{
    private array      $bundleConfig;
    private TagManager $tagManager;

    public function __construct(array $bundleConfig, TagManager $tagManager)
    {
        $this->bundleConfig = $bundleConfig;
        $this->tagManager = $tagManager;
    }

    public function __invoke(Template $template): void
    {
        if (
            !($this->bundleConfig['use_contao_variables'] ?? false)
            && ('fe_page' === $template->getName() || 0 === strpos($template->getName(), 'fe_page_'))
        ) {
            $template->meta = function (array $skip = []) {
                return implode("\n", $this->tagManager->getTags($skip));
            };
        }
    }
}
