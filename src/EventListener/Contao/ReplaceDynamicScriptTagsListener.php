<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\EventListener\Contao;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Hook;

/**
 * @Hook("replaceDynamicScriptTags")
 */
class ReplaceDynamicScriptTagsListener
{
    private array $bundleConfig;

    public function __construct(array $bundleConfig)
    {
        $this->bundleConfig = $bundleConfig;
    }

    public function __invoke(string $buffer): string
    {
        if ($this->bundleConfig['']) {
            $nonce = '';
        }

        if (method_exists(ContaoFramework::class, 'getNonce')) {
            $nonce = '_'.ContaoFramework::getNonce();
        }

        return str_replace("[[TL_HEAD$nonce]]", "[[TL_HEAD$nonce]]".'<link rel="stylesheet" href="assets/custom.css">', $buffer);
    }
}
