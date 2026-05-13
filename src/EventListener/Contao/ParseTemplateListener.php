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

/**
 * @Hook("parseTemplate")
 */
class ParseTemplateListener
{
    private array $bundleConfig;
    private HtmlHeadTagManager $headTagManager;

    public function __construct(array $bundleConfig, HtmlHeadTagManager $headTagManager)
    {
        $this->bundleConfig = $bundleConfig;
        $this->headTagManager = $headTagManager;
    }

    public function __invoke(Template $template): void
    {
        $this->addLegacyMetaMethod($template);
    }

    protected function addLegacyMetaMethod(Template $template): void
    {
        if (!str_starts_with($template->getName(), 'fe_page')) {
            return;
        }

        if (!($this->bundleConfig['use_contao_variables'] ?? false)) {
            $template->meta = function (array $skip = []) {
                return $this->headTagManager->renderTags([
                    'skip_tags' => $skip,
                ]);
            };
        }
    }
}
