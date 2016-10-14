<?php

namespace Keboola\DynamoDbExtractor;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class MissingDbParameterTest extends ExtractorTestCase
{
    /** @var string */
    protected $dataDir = '/tmp/missing-db-params';

    protected function setUp()
    {
        parent::setUp();

        $this->fs->dumpFile($this->dataDir . '/config.json', <<<JSON
{
  "parameters": {
    "db": {
      "accessKeyId": "access key",
      "#secretAccessKey": "access secret",
      "regionName": "eu-central-1"
    }
  }
}
JSON
        );
    }

    public function testMissingParameters()
    {
        $this->expectException(InvalidConfigurationException::class);

        $application = new Application;
        $application->add(new RunCommand);

        $command = $application->find('run');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            'data directory' => $this->dataDir,
        ]);
    }
}