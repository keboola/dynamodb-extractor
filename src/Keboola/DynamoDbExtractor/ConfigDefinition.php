<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class ConfigDefinition implements ConfigurationInterface
{
    private const QUERY_INVALID_NODES = ['dateFilter'];

    private const SCAN_INVALID_NODES = ['conditionExpressionKeys', 'conditionExpressionValues'];

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
                        ->validate()->always(function ($exportItem) {
                            if ($exportItem['mode'] === 'query') {
                                foreach (self::QUERY_INVALID_NODES as $invalidNodes) {
                                    if (isset($exportItem[$invalidNodes])) {
                                        throw new InvalidConfigurationException(
                                            sprintf('Node "%s" is not allowed for query export.', $invalidNodes)
                                        );
                                    }
                                }
                            } else {
                                foreach (self::SCAN_INVALID_NODES as $invalidNodes) {
                                    if (isset($exportItem[$invalidNodes])) {
                                        throw new InvalidConfigurationException(
                                            sprintf('Node "%s" is not allowed for scan export.', $invalidNodes)
                                        );
                                    }
                                }
                            }
                            return $exportItem;
                        })->end()
                        ->children()
                            ->integerNode('id')
                                ->isRequired()
                            ->end()
                            ->scalarNode('name')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->enumNode('mode')
                                ->values(['scan', 'query'])
                                ->defaultValue('scan')
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
