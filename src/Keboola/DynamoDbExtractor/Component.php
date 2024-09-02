<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor;

use Keboola\Component\BaseComponent;
use Keboola\DynamoDbExtractor\Config\Config;
use Keboola\DynamoDbExtractor\Config\ConfigDefinition;
use Psr\Log\LoggerInterface;

class Component extends BaseComponent
{
    private const string ACTION_TEST_CONNECTION = 'testConnection';
    private Extractor $extractor;

    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->extractor = new Extractor($logger, $this->getConfig());
    }

    protected function run(): void
    {
        $this->extractor->actionRun($this->getDataDir());
    }

    protected function testConnection(): array
    {
        return $this->extractor->actionTestConnection();
    }

    /**
     * @return array<string, string>
     */
    protected function getSyncActions(): array
    {
        return [
            self::ACTION_TEST_CONNECTION => 'testConnection',
        ];
    }

    protected function getConfigClass(): string
    {
        return Config::class;
    }

    public function getConfig(): Config
    {
        /** @var Config $config */
        $config = parent::getConfig();
        return $config;
    }

    protected function getConfigDefinitionClass(): string
    {
        return ConfigDefinition::class;
    }
}
