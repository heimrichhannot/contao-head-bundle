<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class HeimrichHannotContaoHeadBundle extends AbstractBundle
{
    protected string $extensionAlias = 'huh_head';

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->booleanNode('use_contao_head')
                    ->defaultFalse()
                    ->info('Use the default head variables for title,base,robots and description instead of removing them from the page template.')
                ->end()
                ->booleanNode('use_contao_variables')
                    ->defaultFalse()
                    ->info('Use the default contao template variables for outputting head tags instead of the meta function.')
                ->end()
            ->end()
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->setParameter('huh_head', $config);

        $container->import('../config/services.yaml');
    }

    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}
