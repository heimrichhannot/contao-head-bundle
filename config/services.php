<?php

use HeimrichHannot\HeadBundle\HeadTag\HeadTagFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure()
    ;

    $services->load('HeimrichHannot\\HeadBundle\\', '../src/{EventListener,Helper,Twig}/*');

    $services->load('HeimrichHannot\\HeadBundle\\Manager\\', '../src/Manager/*')
        ->public()
        ->autoconfigure(false)
    ;

    $services->set(HeadTagFactory::class)
        ->autoconfigure()
    ;
};
