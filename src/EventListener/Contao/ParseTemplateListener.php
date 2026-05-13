<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\EventListener\Contao;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Template;
use HeimrichHannot\HeadBundle\Manager\HtmlHeadTagManager;

#[AsHook('parseTemplate')]
class ParseTemplateListener
{
    public function __construct(private array $bundleConfig, private HtmlHeadTagManager $headTagManager)
    {
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
            $template->meta = (fn(array $skip = []) => $this->headTagManager->renderTags([
                'skip_tags' => $skip,
            ]));
        }
    }
}
