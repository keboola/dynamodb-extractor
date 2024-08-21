<?php

declare(strict_types=1);

use Keboola\DynamoDbExtractor\FunctionalTests\DatadirTest;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

return function (DatadirTest $test): void {
    // simulate full disk
    (new Filesystem())->mkdir($test->testTempDir . '/out/tables');
    $process = Process::fromShellCommandline(
        'ln -s /dev/full ' . $test->testTempDir . '/out/tables/10-movies.json',
    );
    $process->mustRun();
};
