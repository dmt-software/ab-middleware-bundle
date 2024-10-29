<?php

namespace DMT\AbMiddlewareBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class AbMiddlewareBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $container->services()
            ->get('ab_service')
            ->arg('$experiments', $config['experiments'] ?? []);

        $container->services()
            ->get('ab_middleware')
            ->arg('$abService', '@ab_service')
            ->arg('$cookieName', $config['cookie']['name'])
            ->arg('$cookieExpires', $config['cookie']['expires'])
            ->arg('$cookiePath', $config['cookie']['path'])
            ->arg('$cookieDomain', $config['cookie']['domain'])
            ->arg('$cookieSecure', $config['cookie']['secure'])
            ->arg('$cookieHttpOnly', $config['cookie']['http_only'])
            ->arg('$cookieSameSite', $config['cookie']['same_site']);
    }
}
