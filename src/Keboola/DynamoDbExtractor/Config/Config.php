<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor\Config;

use Keboola\Component\Config\BaseConfig;

class Config extends BaseConfig
{
    /**
     * @return array<string, mixed>
     */
    public function getDb(): array
    {
        return $this->getArrayValue(['parameters', 'db']);
    }

    public function getExports(): array
    {
        return $this->getArrayValue(['parameters', 'exports']);
    }
}
