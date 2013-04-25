<?php
namespace Com\AramisAuto\PHPreprocessor\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Com\AramisAuto\PHPreprocessor\PHPreprocessor;

class ApplyCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('apply')
            ->setDescription('Apply dist file to a a directory.')
            ->addArgument('src', InputArgument::REQUIRED, 'Source directory')
            ->addOption('properties', null, InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, 'Properties files')
            ->addOption('reverse', null, InputOption::VALUE_NONE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // For logging
        $stderr = new \SplFileObject('php://stderr', 'w');

        // Parse CLI arguments
        $options = array_merge($input->getArguments(), $input->getOptions());

        if (empty($options['properties'])) {
            throw new \Exception('Please provide at least one properties file.');
        }

        // Instanciate preprocessor
        $p = new PHPreprocessor($stderr);
        $p->apply($options);
    }
}
