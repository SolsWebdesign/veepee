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

class GetVeepeeToken extends Command
{
    const INPUT_KEY_USERNAME = 'username';

    protected $state;
    protected $veePeeConnector;

    public function __construct(
        \Magento\Framework\App\State $state,
        \SolsWebdesign\VeePee\Helper\VeePeeConnector $veePeeConnector,
        $name = 'get_veepee_token'
    ) {
        $this->state = $state;
        $this->veePeeConnector = $veePeeConnector;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('veepee:get:token')
            ->addArgument(
                self::INPUT_KEY_USERNAME,
                InputArgument::REQUIRED,
                'username'
            );
        $this->setDescription('Gets a veepee token for collecting campaigns, batches and orders. username is required');
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
                $username = (string)$input->getArgument(self::INPUT_KEY_USERNAME);
                if(isset($username) && strlen($username) > 0) {
                    $result = $this->veePeeConnector->getTokenCli($username);

                    $message = '<info>'.$result.'</info>';
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
