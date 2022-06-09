<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class RunFullExportUnknownTableTest extends ExtractorTestCase
{
    protected string $dataDir = '/tmp/run-unknown-table';

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
        "name": "10-movies",
        "table": "RandomTable",
        "enabled": true,
        "incremental": true,
        "mapping": {
          "title": "title",
          "year": "year",
          "info.rating": "rating"
        }
      }
    ]
  }
}
JSON
        );
    }

    public function testRun(): void
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
            'Cannot do operations on a non-existent table',
            $commandTester->getDisplay()
        );
    }
}
