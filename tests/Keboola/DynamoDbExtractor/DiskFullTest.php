<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Process\Process;
use Throwable;

class DiskFullTest extends ExtractorTestCase
{
    protected string $dataDir = '/tmp/disk-full';

    protected function setUp(): void
    {
        parent::setUp();

        // simulate full disk
        $this->fs->mkdir($this->dataDir . '/out/tables');
        $process = Process::fromShellCommandline(
            'ln -s /dev/full ' . $this->dataDir . '/out/tables/10-movies.json'
        );
        $process->mustRun();

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

    public function testDiskFull(): void
    {
        $application = new Application;
        $application->add(new RunCommand);

        $command = $application->find('run');
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'command' => $command->getName(),
            'data directory' => $this->dataDir,
        ]);

        $this->assertSame(2, $exitCode);

        $this->assertStringContainsString(
            'No space left on device',
            (string) file_get_contents('/code/error.log')
        );
    }

    public function testDiskFullTestMode(): void
    {
        $this->expectException(Throwable::class);
        $this->expectExceptionMessage('app-errors.ERROR: file_put_contents(): Only');
        $this->expectExceptionMessage('No space left on device');

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
