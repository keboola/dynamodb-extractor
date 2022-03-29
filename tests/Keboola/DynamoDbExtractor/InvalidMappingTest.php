<?php

namespace Keboola\DynamoDbExtractor;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class InvalidMappingTest extends ExtractorTestCase
{
    /** @var string */
    protected $dataDir = '/tmp/run';

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
        "table": "Movies",
        "enabled": true,
        "incremental": false,
        "mapping": {
          "info": "info"
        }
      }
    ]
  }
}
JSON
        );
    }

    public function testExportInvalidMappingBadData(): void
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
        $output = $commandTester->getDisplay();
        $this->assertContains(
            'Error writing \'info\' column: Cannot write object into a column',
            $output
        );
    }
}
