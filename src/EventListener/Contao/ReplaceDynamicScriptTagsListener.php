<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\EventListener\Contao;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Framework\ContaoFramework;
use HeimrichHannot\HeadBundle\Manager\HtmlHeadTagManager;

#[AsHook('replaceDynamicScriptTags')]
class ReplaceDynamicScriptTagsListener
{
    public function __construct(private array $bundleConfig, private readonly HtmlHeadTagManager $headTagManager)
    {
    }

    /**
     * @noinspection PhpUnnecessaryLocalVariableInspection
     */
    public function __invoke(string $buffer): string
    {
        $buffer = $this->addHeadTags($buffer);

        return $buffer;
    }

    private function addHeadTags(string $buffer): string
    {
        if ($this->bundleConfig['use_contao_variables'] ?? false) {
            return $this->replace($buffer, 'TL_HEAD', $this->headTagManager->renderTags());
        }

        return $buffer;
    }
    private function replace(string $buffer, string $tag, string $content): string
    {
        $nonce = '';

        if (method_exists(ContaoFramework::class, 'getNonce')) {
            $nonce = '_' . ContaoFramework::getNonce();
        }

        return str_replace("[[$tag$nonce]]", "[[$tag$nonce]]" . $content, $buffer);
    }
}
