<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/common.php';

use Keboola\DynamoDbExtractor\RunCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\ConsoleOutput;

$application = new Application;
$application->add(new RunCommand);
$application->run(null, new ConsoleOutput());
