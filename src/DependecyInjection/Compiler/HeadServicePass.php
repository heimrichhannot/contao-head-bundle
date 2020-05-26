<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\DependencyInjection\Compiler;

use HeimrichHannot\HeadBundle\Head\TagInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class HeadServicePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $tags = [];
        $definitions = $container->getDefinitions();

        foreach ($definitions as $service => $definition) {
            try {
                $r = $container->getReflectionClass($definition->getClass());

                if (null !== $r && $r->implementsInterface(TagInterface::class)) {
                    $tags[$definition->getClass()] = $service;
                }
            } catch (\Exception $e) {
            }
        }

        $container->setParameter('huh.head.tags', $tags);
    }
}
