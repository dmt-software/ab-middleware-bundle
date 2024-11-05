<?php

namespace DMT\AbMiddlewareBundle;

use DMT\AbMiddleware\AbService;
use DMT\AbMiddleware\AbTwigHelper;
use DMT\AbMiddleware\GaAudienceHelper;
use DMT\AbMiddlewareBundle\EventListener\AbMiddlewareSubscriber;
use Google\Analytics\Admin\V1alpha\Client\AnalyticsAdminServiceClient;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class AbMiddlewareBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('experiments')
                    ->ignoreExtraKeys(false)
                    ->normalizeKeys(false)
                    ->arrayPrototype()
                        ->ignoreExtraKeys(false)
                        ->normalizeKeys(false)
                        ->floatPrototype()
                            ->min(0.0)
                            ->max(1.0)
                            ->isRequired()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('cookie')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('name')->defaultValue('ab-uid')->end()
                        ->scalarNode('expires')->defaultValue('+1 month')->end()
                        ->scalarNode('path')->defaultNull()->end()
                        ->scalarNode('domain')->defaultNull()->end()
                        ->booleanNode('secure')->defaultNull()->end()
                        ->booleanNode('http_only')->defaultTrue()->end()
                        ->scalarNode('same_site')->defaultValue('Lax')->end()
                    ->end()
                ->end()
                ->arrayNode('ga4')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('account_id')->defaultNull()->end()
                        ->arrayNode('property_ids')
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
            ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $services = $container->services();

        $services->set(AbService::class)
            ->arg('$experiments', $config['experiments'])
            ->public();

        $services->set(AbMiddlewareSubscriber::class)
            ->arg('$abService', new ReferenceConfigurator(AbService::class))
            ->arg('$cookieName', $config['cookie']['name'])
            ->arg('$cookieExpires', $config['cookie']['expires'])
            ->arg('$cookiePath', $config['cookie']['path'])
            ->arg('$cookieDomain', $config['cookie']['domain'])
            ->arg('$cookieSecure', $config['cookie']['secure'])
            ->arg('$cookieHttpOnly', $config['cookie']['http_only'])
            ->arg('$cookieSameSite', $config['cookie']['same_site'])
            ->tag('kernel.event_subscriber')
            ->public();

        $services->set(AnalyticsAdminServiceClient::class)
            ->public();

        $services->set(GaAudienceHelper::class)
            ->arg('$abService', new ReferenceConfigurator(AbService::class))
            ->arg('$client', new ReferenceConfigurator(AnalyticsAdminServiceClient::class))
            ->arg('$accountId', $config['ga4']['account_id'])
            ->arg('$propertyIds', $config['ga4']['property_ids'])
            ->arg('$audiencePrefix', $config['ga4']['audience_prefix'])
            ->public();

        $services->set(AbTwigHelper::class)
            ->arg('$abService', new ReferenceConfigurator(AbService::class))
            ->tag('twig.extension');
    }
}
