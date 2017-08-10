<?php

namespace App\DependencyInjection;

use App\Config\BadgesConfig;
use App\Config\ProvidersConfig;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('app');

        $rootNode
            ->children()
                ->append($this->addBadgesNode())
                ->append($this->addProvidersNode())
            ->end()
        ;

        return $treeBuilder;
    }

    protected function addProvidersNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root(ProvidersConfig::KEY_PROVIDERS);

        $node
            ->prototype('array')
                ->isRequired()
                ->normalizeKeys(false)
                ->children()
                    ->scalarNode(ProvidersConfig::KEY_ENTITY)->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode(ProvidersConfig::KEY_REGEX)->isRequired()->cannotBeEmpty()->end()
                    ->arrayNode(ProvidersConfig::KEY_MATCH)
                        ->isRequired()
                        ->requiresAtLeastOneElement()
                        ->normalizeKeys(false)
                        ->useAttributeAsKey('name')
                        ->prototype('scalar')->isRequired()->cannotBeEmpty()->end()
                    ->end()
                    ->arrayNode('fields')
                        ->isRequired()
                        ->requiresAtLeastOneElement()
                        ->normalizeKeys(false)
                        ->useAttributeAsKey('name')
                        ->prototype('scalar')->isRequired()->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    protected function addBadgesNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root(BadgesConfig::KEY_BADGES);

        $node
            ->children()
                ->arrayNode(BadgesConfig::KEY_SEVERITY)
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->normalizeKeys(false)
                    ->prototype('scalar')->isRequired()->cannotBeEmpty()->end()
                ->end()
                ->arrayNode(BadgesConfig::KEY_HTTP)
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->normalizeKeys(false)
                    ->prototype('scalar')->isRequired()->cannotBeEmpty()->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
