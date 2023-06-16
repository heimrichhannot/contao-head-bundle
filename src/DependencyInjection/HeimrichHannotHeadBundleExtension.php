<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\DependencyInjection;

use Contao\CoreBundle\EventListener\DataContainer\DisableCanonicalFieldsListener;
use HeimrichHannot\HeadBundle\EventListener\CanonicalListener;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class HeimrichHannotHeadBundleExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('huh_head', $config);

        if (class_exists(DisableCanonicalFieldsListener::class)) {
            $container->removeDefinition(CanonicalListener::class);
        }
    }

    public function getAlias()
    {
        return 'huh_head';
    }
}
