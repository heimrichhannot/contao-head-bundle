<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\HeadBundle\DependencyInjection\Compiler;


use HeimrichHannot\HeadBundle\Head\TagInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class HeadServicePass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $tags        = [];
        $definitions = $container->getDefinitions();

        foreach ($definitions as $service => $defintion) {

            try {
                $r = $container->getReflectionClass($defintion->getClass());

                if ($r !== null && $r->implementsInterface(TagInterface::class)) {
                    $tags[] = $service;
                }
            } catch (\Exception $e) {

            }
        }

        $container->setParameter('huh.head.tags', $tags);
    }
}