<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class RunQueryFullExportWithPlaceholderTest extends ExtractorTestCase
{
    protected string $dataDir = '/tmp/run';

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
        "incremental": true,
        "mode": "query",
        "keyConditionExpression": "\$yr = :a",
        "expressionAttributeNames": {
          "\$yr": "year"
        },
        "expressionAttributeValues": {
          ":a": "2013"
        },
        "primaryKey": ["title", "year"],
        "mapping": {
          "title": "title",
          "year": "year",
          "info.rating": "rating"
        }
      }
    ]
  }
}
JSON,);
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
        ], ['capture_stderr_separately' => true]);

        $expectedFile = $this->dataDir . '/out/tables/10-movies.csv';
        $expectedManifestFile = $expectedFile . '.manifest';
        $this->assertSame(0, $exitCode);
        $this->assertFileExists($expectedFile);
        $this->assertFileExists($expectedManifestFile);

        $expectedCsv = <<<CSV
"title","year","rating"
"Insidious: Chapter 2","2013","7.1"
"Now You See Me","2013","7.3"
"Prisoners","2013","8.2"
"Rush","2013","8.3"
"The Hunger Games: Catching Fire","2013",""
"This Is the End","2013","7.2"
"Thor: The Dark World","2013",""
"World War Z","2013","7.1"\n
CSV;
        $this->assertEquals($expectedCsv, file_get_contents($expectedFile));

        $expectedManifest = <<<JSON
{"primary_key":["title","year"],"incremental":true}
JSON;
        $this->assertEquals($expectedManifest, file_get_contents($expectedManifestFile));
    }
}
