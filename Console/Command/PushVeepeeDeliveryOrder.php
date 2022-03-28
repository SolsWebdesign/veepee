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

// at the moment we cannot get this to work
// it always gives an "area not set" error
// even when we use $this->state->setAreaCode('frontend');
class PushVeepeeDeliveryOrder extends Command
{
    const INPUT_KEY_VEEPEE_ORDER_ID = 'veepee_order_id';

    protected $state;
    protected $veePeeOrderManager;

    public function __construct(
        \Magento\Framework\App\State $state,
        \SolsWebdesign\VeePee\Helper\VeePeeOrderManager $veePeeOrderManager,
        $name = 'push_veepee_delivery_order'
    ) {
        $this->state = $state;
        $this->veePeeOrderManager = $veePeeOrderManager;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('veepee:push:deliveryorder')
            ->addArgument(
                self::INPUT_KEY_VEEPEE_ORDER_ID,
                InputArgument::REQUIRED,
                'veepeeOrderId'
            );
        $this->setDescription('Pushes one veepee delivery order if products are available and in stock (veepeeOrderId is required).');
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
            // frontend or adminhtml, both of them do not work
            $this->state->emulateAreaCode('adminhtml', function () use ($input, $output) {
                $veepeeOrderId = (int)$input->getArgument(self::INPUT_KEY_VEEPEE_ORDER_ID);
                if (isset($veepeeOrderId) && $veepeeOrderId > 0) {
                    $result = $this->veePeeOrderManager->pushDeliveryOrder($veepeeOrderId);
                    $message = '<info>' . $result . '</info>';
                    $output->writeln($message);
                } else {
                    $message = '<info>VeepeeOrderId is required.</info>';
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
