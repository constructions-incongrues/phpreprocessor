<?php
namespace Com\AramisAuto\PHPreprocessor\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Com\AramisAuto\PHPreprocessor\PHPreprocessor;

class ExtractCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('extract')
            ->setDescription('Extract tokens from given directory')
            ->addOption('src', null, InputOption::VALUE_REQUIRED, 'Source directory', '.')
            ->addOption('exclude-from', null, InputOption::VALUE_REQUIRED, '')
            ->addOption('merge-with', null, InputOption::VALUE_REQUIRED, '')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // For logging
        $stderr = new \SplFileObject('php://stderr', 'w');

        // Parse CLI arguments
        $options = $input->getOptions();

        // Instanciate preprocessor
        $p = new PHPreprocessor($stderr);
        $p->extract($options);
    }
}
