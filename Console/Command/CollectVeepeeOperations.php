<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CollectVeepeeOperations extends Command
{
    protected $state;
    protected $veePeeConnector;

    public function __construct(
        \Magento\Framework\App\State $state,
        \SolsWebdesign\VeePee\Helper\VeePeeConnector $veePeeConnector,
        $name = 'collect_veepee_operations'
    ) {
        $this->state = $state;
        $this->veePeeConnector = $veePeeConnector;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('veepee:collect:operations');
        $this->setDescription('Collects veepee operations.');
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->emulateAreaCode('adminhtml', function () use ($input, $output) {
                $result = $this->veePeeConnector->getOperations();

                $message = '<info>'.$result.'</info>';
                $output->writeln($message);
                return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
            });
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            $output->write($e->getTraceAsString());
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($e->getTraceAsString());
            }
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }
}
