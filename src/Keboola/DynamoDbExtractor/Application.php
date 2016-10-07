<?php

namespace Keboola\DynamoDbExtractor;

use Symfony\Component\Config\Definition\Processor;

class Application
{
    /** @var array */
    private $config;

    /** @var array */
    private $parameters;

    public function __construct(array $config)
    {
        $this->config = $config;

        $this->parameters = (new Processor)->processConfiguration(
            new ConfigDefinition,
            [$this->config['parameters']]
        );
    }

    public function actionTestConnection(): array
    {
        return [
            'status' => 'ok'
        ];
    }
}
