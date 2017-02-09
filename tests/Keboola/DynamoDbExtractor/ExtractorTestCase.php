<?php

namespace Keboola\DynamoDbExtractor;

use Symfony\Component\Filesystem\Filesystem;

abstract class ExtractorTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var Filesystem */
    protected $fs;

    /** @var string */
    protected $dataDir;

    protected function setUp()
    {
        ini_set('error_log', '/code/error.log');

        $this->fs = new Filesystem;
        $this->fs->remove($this->dataDir);
        $this->fs->mkdir($this->dataDir);
    }

    protected function tearDown()
    {
        ini_set('error_log', '');

        $this->fs->remove($this->dataDir);
        $this->fs->remove('/code/error.log');
    }
}
