<?php

namespace Keboola\DynamoDbExtractor;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ConfigFileNotFoundTest extends ExtractorTestCase
{
    /** @var string */
    protected $dataDir = '/tmp/config-file-not-found';

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

        $this->assertSame(2, $exitCode);
        $this->assertContains(
            'Application error',
            $commandTester->getDisplay()
        );
    }
}
