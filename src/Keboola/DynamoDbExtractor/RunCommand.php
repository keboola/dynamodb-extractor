<?php

namespace Keboola\DynamoDbExtractor;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class RunCommand extends Command
{
    protected function configure()
    {
        $this->setName('run');
        $this->setDescription('Runs extractor');
        $this->addArgument('data directory', InputArgument::REQUIRED, 'Data directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dataDirectory = $input->getArgument('data directory');

        $configFile = $dataDirectory . '/config.json';

        if (!file_exists($configFile)) {
            throw new Exception('Config file not found at path ' . $configFile);
        }

        $jsonDecode = new JsonDecode(true);
        $config = $jsonDecode->decode(
            file_get_contents($configFile),
            JsonEncoder::FORMAT
        );

        $extractor = new Extractor($config);
        $action = $config['action'] ?? 'run';

        switch ($action) {
            case 'testConnection':
                $result = $extractor->actionTestConnection();
                $output->write(\json_encode($result));
                break;
            case 'run':
                $outputPath = $dataDirectory . '/out/tables';
                $extractor->actionRun($outputPath);
                break;
            default:
                echo 'Action "' . $action . '" not supported';
                break;
        }
    }
}
