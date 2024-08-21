<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor\Config;

use Keboola\Component\Config\BaseConfigDefinition;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class ConfigDefinition extends BaseConfigDefinition
{
    public const string MODE_SCAN = 'scan';

    public const string MODE_QUERY = 'query';

    private const array QUERY_INVALID_NODES = ['dateFilter'];

    private const array SCAN_INVALID_NODES = [
        'indexName',
        'keyConditionExpression',
        'expressionAttributeNames',
        'expressionAttributeValues',
    ];

    public function getParametersDefinition(): ArrayNodeDefinition
    {
        $parametersNode = parent::getParametersDefinition();

        $parametersNode
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
                            if ($exportItem['mode'] === self::MODE_QUERY) {
                                foreach (self::QUERY_INVALID_NODES as $invalidNodes) {
                                    if (isset($exportItem[$invalidNodes])) {
                                        throw new InvalidConfigurationException(
                                            sprintf('Node "%s" is not allowed for query export.', $invalidNodes),
                                        );
                                    }
                                }
                            } else {
                                foreach (self::SCAN_INVALID_NODES as $invalidNodes) {
                                    if (isset($exportItem[$invalidNodes])) {
                                        throw new InvalidConfigurationException(
                                            sprintf('Node "%s" is not allowed for scan export.', $invalidNodes),
                                        );
                                    }
                                }
                            }
                            return $exportItem;
                        })->end()
                        ->children()
                            ->integerNode('id')->isRequired()->end()
                            ->scalarNode('name')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->enumNode('mode')
                                ->values([self::MODE_SCAN, self::MODE_QUERY])
                                ->defaultValue(self::MODE_SCAN)
                            ->end()
                            ->scalarNode('table')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('index')->cannotBeEmpty()->end()
                            ->integerNode('limit')->end()
                            ->variableNode('dateFilter')->end()
                            ->scalarNode('indexName')->end()
                            ->scalarNode('keyConditionExpression')->end()
                            ->variableNode('expressionAttributeValues')->end()
                            ->variableNode('expressionAttributeNames')->end()
                            ->booleanNode('enabled')->defaultValue(true)->end()
                            ->booleanNode('incremental')->isRequired()->end()
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

        return $parametersNode;
    }
}
