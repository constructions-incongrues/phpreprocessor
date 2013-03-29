<?php
namespace Com\AramisAuto\PHPreprocessor\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Com\AramisAuto\PHPreprocessor\PHPreprocessor;

class ApplyCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('apply')
            ->setDescription('Apply dist file')
            ->addOption(
               'src',
               null,
               InputOption::VALUE_REQUIRED,
               'Source directory',
               '.'
            )
            ->addOption(
               'properties',
               null,
               InputOption::VALUE_REQUIRED,
               'Properties files'
            )
            ->addOption(
               'exclude-from',
               null,
               InputOption::VALUE_NONE,
               ''
            )
            ->addOption(
               'merge-with',
               null,
               InputOption::VALUE_NONE,
               ''
            )
            ->addOption(
               'reverse',
               null,
               InputOption::VALUE_NONE,
               ''
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // For logging
        $stderr = new \SplFileObject('php://stderr', 'w');

        // Parse CLI arguments
        $command = $input->getFirstArgument();

        $options = $input->getOptions();

        // Instanciate preprocessor
        $p = new PHPreprocessor($stderr);

        // Call appropriate command
        call_user_func(array($p, $command), $options);
    }
}
