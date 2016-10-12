<?php

namespace Keboola\DynamoDbExtractor;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

class TestConnectionSuccessTest extends \PHPUnit_Framework_TestCase
{
    /** @var Filesystem */
    private $fs;

    /** @var string */
    private $dataDir = '/tmp/test-connection';

    protected function setUp()
    {
        $this->fs = new Filesystem;
        $this->fs->remove($this->dataDir);
        $this->fs->mkdir($this->dataDir);
        $this->fs->dumpFile($this->dataDir . '/config.json', <<<JSON
{
  "action": "testConnection",
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

    public function testConnection()
    {
        $application = new Application;
        $application->add(new RunCommand);

        $command = $application->find('run');
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'command' => $command->getName(),
            'data directory' => $this->dataDir,
        ]);

        $output = $commandTester->getDisplay();

        $expected = [
            'status' => 'ok',
        ];

        $this->assertSame(0, $exitCode);
        $this->assertSame($expected, \json_decode($output, true));
    }
}
