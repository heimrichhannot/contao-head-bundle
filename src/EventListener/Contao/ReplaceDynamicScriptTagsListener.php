<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\EventListener\Contao;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use HeimrichHannot\HeadBundle\Manager\HtmlHeadTagManager;

/**
 * @Hook("replaceDynamicScriptTags")
 */
class ReplaceDynamicScriptTagsListener
{
    private array              $bundleConfig;
    private HtmlHeadTagManager $headTagManager;

    public function __construct(array $bundleConfig, HtmlHeadTagManager $headTagManager)
    {
        $this->bundleConfig = $bundleConfig;
        $this->headTagManager = $headTagManager;
    }

    public function __invoke(string $buffer): string
    {
        if ($this->bundleConfig['use_contao_variables'] ?? false) {
            $nonce = '';

            if (method_exists(ContaoFramework::class, 'getNonce')) {
                $nonce = '_'.ContaoFramework::getNonce();
            }

            $meta = $this->headTagManager->renderTags();

            $buffer = str_replace("[[TL_HEAD$nonce]]", "[[TL_HEAD$nonce]]".$meta, $buffer);
        }

        return $buffer;
    }
}
