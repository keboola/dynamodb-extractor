<?php

namespace Keboola\DynamoDbExtractor;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class RunCommand extends Command
{
    protected function configure()
    {
        $this->setName('run');
        $this->setDescription('Runs extractor');
        $this->addArgument('data directory', InputArgument::REQUIRED, 'Data directory');
        $this->addOption('test-mode', null, null, 'Test mode');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dataDirectory = $input->getArgument('data directory');
        $testMode = $input->getOption('test-mode');

        try {
            $configFile = $dataDirectory . '/config.json';

            if (!file_exists($configFile)) {
                throw new \Exception('Config file not found at path ' . $configFile);
            }

            $outputPath = $dataDirectory . '/out/tables';
            (new Filesystem())->mkdir($outputPath);

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
                    $extractor->actionRun($outputPath);
                    break;
                default:
                    echo 'Action "' . $action . '" not supported';
                    break;
            }
            return 0;
        } catch (InvalidConfigurationException | UserException $e) {
            if ($testMode === true) {
                throw $e;
            }
            $output->writeln($e->getMessage());
            return 1;
        } catch (\Exception $e) {
            if ($testMode === true) {
                throw $e;
            }
            $output->writeln('Application error');
            return 2;
        }
    }
}
