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
use HeimrichHannot\HeadBundle\Manager\JsonLdManager;

/**
 * @Hook("replaceDynamicScriptTags")
 */
class ReplaceDynamicScriptTagsListener
{
    private array              $bundleConfig;
    private HtmlHeadTagManager $headTagManager;
    private JsonLdManager      $jsonLdManager;

    public function __construct(array $bundleConfig, HtmlHeadTagManager $headTagManager, JsonLdManager $jsonLdManager)
    {
        $this->bundleConfig = $bundleConfig;
        $this->headTagManager = $headTagManager;
        $this->jsonLdManager = $jsonLdManager;
    }

    public function __invoke(string $buffer): string
    {
        $buffer = $this->addJsonLs($buffer);

        if ($this->bundleConfig['use_contao_variables'] ?? false) {
            $meta = $this->headTagManager->renderTags();
            $buffer = $this->replace($buffer, 'TL_HEAD', $meta);
        }

        return $buffer;
    }

    private function addJsonLs(string $buffer): string
    {
        return $this->replace($buffer, 'TL_BODY', $this->jsonLdManager->collectFinalScriptFromGraphs());
    }

    private function replace(string $buffer, string $tag, string $content): string
    {
        $nonce = '';

        if (method_exists(ContaoFramework::class, 'getNonce')) {
            $nonce = '_'.ContaoFramework::getNonce();
        }

        return str_replace("[[$tag$nonce]]", "[[$tag$nonce]]".$content, $buffer);
    }
}
