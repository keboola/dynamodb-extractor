<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class UnknownActionTest extends ExtractorTestCase
{
    protected string $dataDir = '/tmp/unknown-action';

    protected function setUp(): void
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
JSON,);
    }

    public function testUnknownAction(): void
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
        $this->assertStringContainsString('Action "unknown-action" not supported', $commandTester->getDisplay());
    }
}
