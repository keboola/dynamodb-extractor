<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Throwable;

class ConfigFileNotFoundTest extends ExtractorTestCase
{
    protected string $dataDir = '/tmp/config-file-not-found';

    public function testConfigFileNotFound(): void
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
            'app-errors.ERROR: Config file not found at path /tmp/config-file-not-found/config.json',
            file_get_contents('/code/error.log')
        );
    }

    public function testConfigFileNotFoundTestMode(): void
    {
        $this->expectException(Throwable::class);
        $this->expectExceptionMessage('Config file not found');

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
