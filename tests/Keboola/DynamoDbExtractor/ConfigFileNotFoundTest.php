<?php

namespace Keboola\DynamoDbExtractor;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ConfigFileNotFoundTest extends ExtractorTestCase
{
    /** @var string */
    protected $dataDir = '/tmp/config-file-not-found';

    public function testConfigFileNotFound()
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

        $this->assertContains(
            'app-errors.ERROR: Config file not found at path /tmp/config-file-not-found/config.json',
            file_get_contents('/code/error.log')
        );
    }

    public function testConfigFileNotFoundTestMode()
    {
        $this->expectException(\Exception::class);
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
