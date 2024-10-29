<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use DMT\AbMiddleware\AbService;
use DMT\AbMiddlewareBundle\EventListener\AbMiddlewareListener;

return function (ContainerConfigurator $container): void
{
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure();

    $services->load('DMT\\AbMiddlewareBundle\\', '../src/')
        ->exclude('../src/{DependencyInjection,Entity,Migrations,Tests,Kernel.php}');

    $services->set('ab_middleware', AbMiddlewareListener::class)
        ->public();

    $services->load('DMT\\AbMiddleware\\', '../vendor/dmt-software/ab-middleware/src/')
        ->exclude('../vendor/dmt-software/ab-middleware/src/{DependencyInjection,Entity,Migrations,Tests,Kernel.php}');

    $services->set('ab_service', AbService::class)
        ->public();
};
