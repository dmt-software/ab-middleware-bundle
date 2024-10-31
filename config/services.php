<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use DMT\AbMiddleware\AbService;
use DMT\AbMiddlewareBundle\EventListener\AbMiddlewareSubscriber;

return function (ContainerConfigurator $container): void
{
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure();

    $abMiddlewarePath = '../vendor/dmt-software/ab-middleware/src/';

    if (file_exists($abMiddlewarePath)) {
        $abMiddlewarePath = '../../../dmt-software/ab-middleware/src/';
    }

    $services->load('DMT\\AbMiddleware\\', $abMiddlewarePath)
        ->exclude($abMiddlewarePath . '{DependencyInjection,Entity,Migrations,Tests,Kernel.php}');

    $services->set('ab_service', AbService::class)
        ->public();

    $services->load('DMT\\AbMiddlewareBundle\\', '../src/')
        ->exclude('../src/{DependencyInjection,Entity,Migrations,Tests,Kernel.php}');

    $services->set('ab_middleware', AbMiddlewareSubscriber::class)
        ->public();
};
