<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/common.php';

use Symfony\Component\Console\Application;
use Keboola\DynamoDbExtractor\RunCommand;

$application = new Application;
$application->add(new RunCommand);
$application->run();
