<?php namespace Pckg\Pendo\Console;

use Pckg\Framework\Console\Command;
use Pckg\Pendo\Entity\Companies;
use Pckg\Pendo\Service\Fiscalization\Invoice;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class EchoPurh
 *
 * @package Pckg\Pendo\Console
 */
class EchoPurh extends Command
{

    protected function configure()
    {
        $this->setName('purh:echo')
             ->setDescription('Check purh echo call')
             ->addOptions([
                              'company' => 'Company id',
                          ], InputOption::VALUE_REQUIRED);
    }

    public function handle()
    {
        $company = (new Companies())->where('id', $this->option('company'))
                                    ->oneOrFail();

        $invoice = new Invoice(123, 22.44, 22.44, date('Y-m-d H:i:s', strtotime('-3 hours')));

        /**
         * Create business.
         */
        $business = $company->createFiscalizationBusiness();
        $fiscalizationService = $company->createFiscalizationService($business, $invoice);

        /**
         * Create echo message and throw exception if something is not ok.
         * Echo works.
         */
        $fiscalizationService->createEchoMsg();
        $fiscalizationService->postXml();
        $response = $fiscalizationService->getXmlResponse();
        echo $response;

        /**
         * Create business message and throw exception if something is not ok.
         * Business is not available anymore, it's manually created.
         */
        /*$fiscalizationService->createBusinessMsg();
        $fiscalizationService->postXml();
        $response = $fiscalizationService->getXmlResponse();
        echo $response;*/

        /**
         * Create invoice message and throw exception if something is not ok.
         * Invoice works.
         */
        $fiscalizationService->createInvoiceMsg();
        $fiscalizationService->postXml();
        $response = $fiscalizationService->getXmlResponse();
        echo $response;
    }

}