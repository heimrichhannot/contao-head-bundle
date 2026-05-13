<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle;

use HeimrichHannot\HeadBundle\DependencyInjection\HeimrichHannotHeadBundleExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotContaoHeadBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new HeimrichHannotHeadBundleExtension();
    }

    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}
