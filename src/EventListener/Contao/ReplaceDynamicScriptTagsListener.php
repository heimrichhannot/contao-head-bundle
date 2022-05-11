<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\EventListener\Contao;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use HeimrichHannot\HeadBundle\Manager\TagManager;

/**
 * @Hook("replaceDynamicScriptTags")
 */
class ReplaceDynamicScriptTagsListener
{
    private array      $bundleConfig;
    private TagManager $tagManager;

    public function __construct(array $bundleConfig, TagManager $tagManager)
    {
        $this->bundleConfig = $bundleConfig;
        $this->tagManager = $tagManager;
    }

    public function __invoke(string $buffer): string
    {
        if ($this->bundleConfig['use_contao_variables'] ?? false) {
            $nonce = '';

            if (method_exists(ContaoFramework::class, 'getNonce')) {
                $nonce = '_'.ContaoFramework::getNonce();
            }

            $meta = implode("\n", $this->tagManager->getTags());

            $buffer = str_replace("[[TL_HEAD$nonce]]", "[[TL_HEAD$nonce]]".$meta, $buffer);
        }

        return $buffer;
    }
}
