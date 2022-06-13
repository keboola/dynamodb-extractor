<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor;

use Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    /**
     * @dataProvider validConfigDataProvider
     */
    public function testValidConfig(array $config): void
    {
        $parameters = (new Processor)->processConfiguration(
            new ConfigDefinition,
            [$config['parameters']]
        );

        self::assertEquals($config['parameters'], $parameters);
    }

    /**
     * @dataProvider invalidConfigDataProvider
     */
    public function testInvalidConfig(array $config, string $exceptionMessage): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage($exceptionMessage);
        (new Processor)->processConfiguration(
            new ConfigDefinition,
            [$config['parameters']]
        );
    }

    public function validConfigDataProvider(): Generator
    {
        yield 'min-config' => [
            [
                'parameters' => [
                    'db' => [
                        'endpoint' => 'dynamoUrl',
                        'accessKeyId' => 'key',
                        '#secretAccessKey' => 'secret',
                        'regionName' => 'eu-central-1',
                    ],
                    'exports' => [
                        [
                            'id' => 1234,
                            'name' => 'testName',
                            'table' => 'testTable',
                            'incremental' => false,
                            'mode' => 'scan',
                            'enabled' => true,
                            'primaryKey' => [],
                            'mapping' => [
                                'id' => 'id',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'query-reading' => [
            [
                'parameters' => [
                    'db' => [
                        'endpoint' => 'dynamoUrl',
                        'accessKeyId' => 'key',
                        '#secretAccessKey' => 'secret',
                        'regionName' => 'eu-central-1',
                    ],
                    'exports' => [
                        [
                            'id' => 1234,
                            'name' => 'testName',
                            'table' => 'testTable',
                            'incremental' => false,
                            'mode' => 'query',
                            'keyConditionExpression' => 'acb',
                            'expressionAttributeValues' => [],
                            'expressionAttributeNames' => [],
                            'enabled' => true,
                            'primaryKey' => [],
                            'mapping' => [
                                'id' => 'id',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function invalidConfigDataProvider(): Generator
    {
        yield 'bad-scan-params-1' => [
            [
                'parameters' => [
                    'db' => [
                        'endpoint' => 'dynamoUrl',
                        'accessKeyId' => 'key',
                        '#secretAccessKey' => 'secret',
                        'regionName' => 'eu-central-1',
                    ],
                    'exports' => [
                        [
                            'id' => 1234,
                            'name' => 'testName',
                            'table' => 'testTable',
                            'incremental' => false,
                            'mode' => 'scan',
                            'keyConditionExpression' => 'acb',
                            'expressionAttributeNames' => [],
                            'expressionAttributeValues' => [],
                            'enabled' => true,
                            'primaryKey' => [],
                            'mapping' => [
                                'id' => 'id',
                            ],
                        ],
                    ],
                ],
            ],
            'Node "keyConditionExpression" is not allowed for scan export.',
        ];

        yield 'bad-scan-params-2' => [
            [
                'parameters' => [
                    'db' => [
                        'endpoint' => 'dynamoUrl',
                        'accessKeyId' => 'key',
                        '#secretAccessKey' => 'secret',
                        'regionName' => 'eu-central-1',
                    ],
                    'exports' => [
                        [
                            'id' => 1234,
                            'name' => 'testName',
                            'table' => 'testTable',
                            'incremental' => false,
                            'mode' => 'scan',
                            'expressionAttributeNames' => [],
                            'expressionAttributeValues' => [],
                            'enabled' => true,
                            'primaryKey' => [],
                            'mapping' => [
                                'id' => 'id',
                            ],
                        ],
                    ],
                ],
            ],
            'Node "expressionAttributeNames" is not allowed for scan export.',
        ];

        yield 'bad-scan-params-3' => [
            [
                'parameters' => [
                    'db' => [
                        'endpoint' => 'dynamoUrl',
                        'accessKeyId' => 'key',
                        '#secretAccessKey' => 'secret',
                        'regionName' => 'eu-central-1',
                    ],
                    'exports' => [
                        [
                            'id' => 1234,
                            'name' => 'testName',
                            'table' => 'testTable',
                            'incremental' => false,
                            'mode' => 'scan',
                            'expressionAttributeValues' => [],
                            'enabled' => true,
                            'primaryKey' => [],
                            'mapping' => [
                                'id' => 'id',
                            ],
                        ],
                    ],
                ],
            ],
            'Node "expressionAttributeValues" is not allowed for scan export.',
        ];

        yield 'bad-scan-params-4' => [
            [
                'parameters' => [
                    'db' => [
                        'endpoint' => 'dynamoUrl',
                        'accessKeyId' => 'key',
                        '#secretAccessKey' => 'secret',
                        'regionName' => 'eu-central-1',
                    ],
                    'exports' => [
                        [
                            'id' => 1234,
                            'name' => 'testName',
                            'table' => 'testTable',
                            'incremental' => false,
                            'mode' => 'scan',
                            'indexName' => 'index',
                            'enabled' => true,
                            'primaryKey' => [],
                            'mapping' => [
                                'id' => 'id',
                            ],
                        ],
                    ],
                ],
            ],
            'Node "indexName" is not allowed for scan export.',
        ];

        yield 'bad-query-params' => [
            [
                'parameters' => [
                    'db' => [
                        'endpoint' => 'dynamoUrl',
                        'accessKeyId' => 'key',
                        '#secretAccessKey' => 'secret',
                        'regionName' => 'eu-central-1',
                    ],
                    'exports' => [
                        [
                            'id' => 1234,
                            'name' => 'testName',
                            'table' => 'testTable',
                            'incremental' => false,
                            'mode' => 'query',
                            'dateFilter' => [],
                            'enabled' => true,
                            'primaryKey' => [],
                            'mapping' => [
                                'id' => 'id',
                            ],
                        ],
                    ],
                ],
            ],
            'Node "dateFilter" is not allowed for query export.',
        ];
    }
}
