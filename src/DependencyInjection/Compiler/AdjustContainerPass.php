<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\DependencyInjection\Compiler;

use Contao\CoreBundle\EventListener\DataContainer\DisableCanonicalFieldsListener;
use HeimrichHannot\HeadBundle\EventListener\CanonicalListener;
use HeimrichHannot\HeadBundle\Head\TagInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AdjustContainerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (class_exists(DisableCanonicalFieldsListener::class)) {
            $container->removeDefinition(CanonicalListener::class);
        }

        $this->processLegacy($container);
    }

    /**
     * @todo remove in next major version
     *
     * @noinspection PhpDeprecationInspection
     */
    private function processLegacy(ContainerBuilder $container)
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
