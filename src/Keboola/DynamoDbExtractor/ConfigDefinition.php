<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConfigDefinition implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('parameters');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('db')
                    ->children()
                        ->scalarNode('endpoint')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('accessKeyId')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('#secretAccessKey')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('regionName')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('exports')
                    ->prototype('array')
                        ->children()
                            ->integerNode('id')
                                ->isRequired()
                            ->end()
                            ->scalarNode('name')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('table')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('index')
                                ->cannotBeEmpty()
                            ->end()
                            ->integerNode('limit')
                            ->end()
                            ->variableNode('dateFilter')
                            ->end()
                            ->booleanNode('enabled')
                                ->defaultValue(true)
                            ->end()
                            ->booleanNode('incremental')
                                ->isRequired()
                            ->end()
                            ->arrayNode('primaryKey')
                                ->scalarPrototype()
                                ->end()
                            ->end()
                            ->variableNode('mapping')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
