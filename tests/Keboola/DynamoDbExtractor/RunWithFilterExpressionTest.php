<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class RunWithFilterExpressionTest extends ExtractorTestCase
{
    protected string $dataDir = '/tmp/run-with-filter-expression';

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
        "dateFilter": {
          "field": "year",
          "format": "Y",
          "value": "2014-01-01"
        },
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
            '--test-mode' => true,
        ]);

        $expectedFile = $this->dataDir . '/out/tables/10-movies.csv';
        $expectedManifestFile = $expectedFile . '.manifest';

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($expectedFile);
        $this->assertFileExists($expectedManifestFile);

        $expectedCsv = <<<CSV
"title","year","rating"
"Transformers: Age of Extinction","2014",""
"X-Men: Days of Future Past","2014",""\n
CSV;
        $this->assertEquals($expectedCsv, file_get_contents($expectedFile));

        $expectedManifest = <<<JSON
{"primary_key":[],"incremental":false}
JSON;
        $this->assertEquals($expectedManifest, file_get_contents($expectedManifestFile));
    }
}
