<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Twig\Extension;

use Contao\CoreBundle\Twig\Runtime\SchemaOrgRuntime as ContaoSchemaOrgRuntime;
use HeimrichHannot\HeadBundle\Twig\Runtime\SchemaOrgRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class HeadBundleTwigExtension extends AbstractExtension
{
    public function getFunctions()
    {
        if (class_exists(ContaoSchemaOrgRuntime::class)) {
            return [];
        }

        return [
            new TwigFunction(
                'add_schema_org',
                [SchemaOrgRuntime::class, 'add']
            ),
        ];
    }
}
