<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle;

use HeimrichHannot\HeadBundle\DependencyInjection\Compiler\AdjustContainerPass;
use HeimrichHannot\HeadBundle\DependencyInjection\HeimrichHannotHeadBundleExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotContaoHeadBundle extends Bundle
{
    /**
     * Builds the bundle.
     *
     * It is only ever called once when the cache is empty.
     *
     * This method can be overridden to register compilation passes,
     * other extensions, ...
     *
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AdjustContainerPass());
    }

    public function getContainerExtension()
    {
        return new HeimrichHannotHeadBundleExtension();
    }
}
