<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class NoEnabledExportsTest extends ExtractorTestCase
{
    protected string $dataDir = '/tmp/no-enabled-exports';

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
        "name": "not-enabled",
        "table": "Movies",
        "incremental": true,
        "enabled": false,
        "mapping": {
          "title": "title"
        }
      }
    ]
  }
}
JSON,);
    }

    public function testDuplicateExportNames(): void
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
    }

    public function testDuplicateExportNamesTestMode(): void
    {
        $this->expectException(UserException::class);
        $this->expectExceptionMessage('Please enable at least one export');

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
