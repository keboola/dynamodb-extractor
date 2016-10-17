<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use Keboola\DynamoDbExtractor\RunCommand;

$application = new Application;
$application->add(new RunCommand);
$application->run();
