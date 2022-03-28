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

class CollectVeepeeBatches extends Command
{
    const INPUT_KEY_OPERATION_CODE = 'code';

    protected $state;
    protected $veePeeConnector;

    public function __construct(
        \Magento\Framework\App\State $state,
        \SolsWebdesign\VeePee\Helper\VeePeeConnector $veePeeConnector,
        $name = 'collect_veepee_batches'
    )
    {
        $this->state = $state;
        $this->veePeeConnector = $veePeeConnector;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('veepee:collect:batches')
            ->addArgument(
                self::INPUT_KEY_OPERATION_CODE,
                InputArgument::REQUIRED,
                'code'
            );
        $this->setDescription('Collects batches for a campaign. (operation)code is required');
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
                $code = (string)$input->getArgument(self::INPUT_KEY_OPERATION_CODE);
                if (isset($code) && strlen($code) > 0) {
                    $result = $this->veePeeConnector->getBatches($code);

                    $message = '<info>' . $result . '</info>';
                    $output->writeln($message);
                } else {
                    $message = '<info>Username is required.</info>';
                    $output->writeln($message);
                }
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
