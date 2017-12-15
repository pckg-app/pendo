<?php namespace Pckg\Pendo\Console;

use Pckg\Framework\Console\Command;
use Pckg\Pendo\Entity\Companies;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class EchoFurs
 *
 * @package Pckg\Pendo\Console
 */
class EchoFurs extends Command
{

    protected function configure()
    {
        $this->setName('furs:echo')
             ->setDescription('Check furs echo call')
             ->addOptions([
                              'company' => 'Company id',
                          ], InputOption::VALUE_REQUIRED);
    }

    public function handle()
    {
        $company = (new Companies())->where('id', $this->option('company'))
                                    ->oneOrFail();

        /**
         * Create business.
         */
        $business = $company->createFiscalizationBusiness();
        $furs = $company->createFiscalizationService($business);

        /**
         * Create echo message and throw exception if something is not ok.
         */
        $furs->createEchoMsg();
        $furs->postXml();
        $response = $furs->getXmlResponse();
        echo $response;

        /**
         * Create business message and throw exception if something is not ok.
         */
        $furs->createBusinessMsg();
        $furs->postXml();
        $response = $furs->getXmlResponse();
        echo $response;
    }

}