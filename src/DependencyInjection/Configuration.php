<?php
/** @author Adam Pawełczyk */

namespace ATPawelczyk\Elastic\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package ATPawelczyk\Elastic\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(ElasticExtension::ALIAS);
        $treeBuilder
            ->getRootNode()
            ->children()
                ->scalarNode('client')->isRequired()->end()
                ->scalarNode('prefix')->defaultValue('')->end()
                ->scalarNode('bus')->defaultNull()->end()
                ->arrayNode('indexes')
                    ->useAttributeAsKey('key')
                    ->arrayPrototype()
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function ($v) {
                                return ['class' => $v];
                            })
                        ->end()
                        ->children()
                            ->scalarNode('name')->defaultValue('')->end()
                            ->scalarNode('class')->defaultValue('')->end()
                            ->scalarNode('prefix')->defaultValue('')->end()
                            ->variableNode('settings')->defaultValue([])->end()
                        ->end()
                        ->append($this->getPropertiesNode())
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    private function getPropertiesNode()
    {
        $node = $this->createTreeBuilderNode('properties');
        $node
            ->useAttributeAsKey('name')
            ->prototype('variable')
            ->treatNullLike([])
        ;

        return $node;
    }

    private function createTreeBuilderNode(string $name)
    {
        return (new TreeBuilder($name))->getRootNode();
    }
}
