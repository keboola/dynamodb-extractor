<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class MissingDateFilterParametersTest extends ExtractorTestCase
{
    protected string $dataDir = '/tmp/missing-date-filter-parameters';

    protected function setUp(): void
    {
        parent::setUp();

        $this->fs->dumpFile($this->dataDir . '/config.json', <<<JSON
{
  "parameters": {
    "db": {
      "endpoint": "http://dynamodb:8000",
      "accessKeyId": "key",
      "#secretAccessKey": "secret",
      "regionName": "eu-central-1"
    },
    "exports": [
      {
        "id": 1,
        "name": "movies",
        "table": "Movies",
        "enabled": true,
        "incremental": true,
        "mapping": {
          "title": "title",
          "year": "year",
          "info.rating": "rating"
        },
        "dateFilter": {
          "field": "year",
          "format": "Y"
        }
      }
    ]
  }
}
JSON
        );
    }

    public function testMissingDateFilterParameters(): void
    {
        $application = new Application;
        $application->add(new RunCommand);

        $command = $application->find('run');
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'command' => $command->getName(),
            'data directory' => $this->dataDir,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString(
            'Please check if "dateFilter" contains all required parameters (field, format and value)'
            . ' in "movies" export',
            $commandTester->getDisplay()
        );
    }

    public function testMissingDateFilterParametersTestMode(): void
    {
        $this->expectException(UserException::class);
        $this->expectExceptionMessage(
            'Please check if "dateFilter" contains all required parameters (field, format and value)'
            . ' in "movies" export'
        );

        $application = new Application;
        $application->add(new RunCommand);

        $command = $application->find('run');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            'data directory' => $this->dataDir,
            '--test-mode' => true,
        ]);
    }
}
