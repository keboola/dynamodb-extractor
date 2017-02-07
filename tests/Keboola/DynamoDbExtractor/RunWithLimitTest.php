<?php

namespace Keboola\DynamoDbExtractor;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class RunWithLimitTest extends ExtractorTestCase
{
    /** @var string */
    protected $dataDir = '/tmp/run-with-limit';

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
        "name": "10-movies",
        "table": "Movies",
        "enabled": true,
        "incremental": true,
        "limit": 8,
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

    public function testRun()
    {
        $application = new Application;
        $application->add(new RunCommand);

        $command = $application->find('run');
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'command' => $command->getName(),
            'data directory' => $this->dataDir,
        ]);

        $expectedFile = $this->dataDir . '/out/tables/10-movies.csv';

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($expectedFile);

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
    }
}