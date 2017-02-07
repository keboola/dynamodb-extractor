<?php

namespace Keboola\DynamoDbExtractor;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class DuplicateExportNamesTest extends ExtractorTestCase
{
    /** @var string */
    protected $dataDir = '/tmp/duplicate-export-names';

    protected function setUp()
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
        "name": "same-name",
        "table": "Movies",
        "incremental": true,
        "mapping": {
          "title": "title"
        }
      },
      {
        "id": 2,
        "name": "same-name",
        "table": "Movies",
        "incremental": true,
        "mapping": {
          "title": "title"
        }
      }
    ]
  }
}
JSON
        );
    }

    public function testDuplicateExportNames()
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

    public function testDuplicateExportNamesTestMode()
    {
        $this->expectException(UserException::class);
        $this->expectExceptionMessage('Please remove duplicate export names');

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
