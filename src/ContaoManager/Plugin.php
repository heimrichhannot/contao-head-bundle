<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ConfigPluginInterface;
use HeimrichHannot\HeadBundle\HeimrichHannotContaoHeadBundle;
use Symfony\Component\Config\Loader\LoaderInterface;

class Plugin implements BundlePluginInterface, ConfigPluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(HeimrichHannotContaoHeadBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class, 'modal']),
        ];
    }

    /**
     * Allows a plugin to load container configuration.
     */
    public function registerContainerConfiguration(LoaderInterface $loader, array $managerConfig)
    {
        $loader->load('@HeimrichHannotContaoHeadBundle/Resources/config/listener.yml');
        $loader->load('@HeimrichHannotContaoHeadBundle/Resources/config/services.yml');
        $loader->load('@HeimrichHannotContaoHeadBundle/Resources/config/tags.yml');
    }
}
