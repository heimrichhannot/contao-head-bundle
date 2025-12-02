<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('huh_head');

        $treeBuilder->getRootNode()
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

        return $treeBuilder;
    }
}
