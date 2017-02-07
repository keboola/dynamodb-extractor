<?php

namespace Keboola\DynamoDbExtractor;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class UnknownActionTest extends ExtractorTestCase
{
    /** @var string */
    protected $dataDir = '/tmp/unknown-action';

    protected function setUp()
    {
        parent::setUp();

        $this->fs->dumpFile($this->dataDir . '/config.json', <<<JSON
{
  "action": "unknown-action",
  "parameters": {
    "db": {
      "endpoint": "http://dynamodb:8000",
      "accessKeyId": "access key",
      "#secretAccessKey": "access secret",
      "regionName": "eu-central-1"
    }
  }
}
JSON
        );
    }

    public function testUnknownAction()
    {
        $application = new Application;
        $application->add(new RunCommand);

        $command = $application->find('run');
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'command' => $command->getName(),
            'data directory' => $this->dataDir,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertContains('Action "unknown-action" not supported', $commandTester->getDisplay());
    }
}
