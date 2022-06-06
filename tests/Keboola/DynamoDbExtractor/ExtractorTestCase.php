<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class ExtractorTestCase extends TestCase
{
    protected Filesystem $fs;

    protected string $dataDir;

    protected function setUp(): void
    {
        ini_set('error_log', '/code/error.log');

        $this->fs = new Filesystem;
        $this->fs->remove($this->dataDir);
        $this->fs->mkdir($this->dataDir);
    }

    protected function tearDown(): void
    {
        ini_set('error_log', '');

        $this->fs->remove($this->dataDir);
        $this->fs->remove('/code/error.log');
    }
}
