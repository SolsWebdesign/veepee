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

class CollectVeepeeDeliveryOrders extends Command
{
    const INPUT_KEY_BATCH_ID = 'batch_id';

    protected $state;
    protected $veePeeConnector;

    public function __construct(
        \Magento\Framework\App\State $state,
        \SolsWebdesign\VeePee\Helper\VeePeeConnector $veePeeConnector,
        $name = 'collect_veepee_delivery_orders'
    ) {
        $this->state = $state;
        $this->veePeeConnector = $veePeeConnector;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('veepee:collect:orders')
            ->addArgument(
                self::INPUT_KEY_BATCH_ID,
                InputArgument::REQUIRED,
                'batchId'
            );
        $this->setDescription('Collects veepee delivery orders for a certain batch (batchId is required).');
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

                $batchId = (int)$input->getArgument(self::INPUT_KEY_BATCH_ID);
                if (isset($batchId) && $batchId > 0) {
                    $result = $this->veePeeConnector->getDeliveryOrdersForBatch($batchId);

                    $message = '<info>' . $result . '</info>';
                    $output->writeln($message);
                } else {
                    $message = '<info>BatchId is required.</info>';
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

