<?php

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition): void {
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
        ->end();
};
