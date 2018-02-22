<?php namespace Pckg\Pendo\Console;

use Pckg\Framework\Console\Command;
use Pckg\Pendo\Entity\Companies;
use Pckg\Pendo\Service\Fiscalization\Invoice;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class EchoFurs
 *
 * @package Pckg\Pendo\Console
 */
class InvoiceFurs extends Command
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

        $invoice = new Invoice(123, 22.44, 22.44, date('Y-m-d H:i:s', strtotime('-3 hours')));

        /**
         * Create business.
         */
        $business = $company->createFiscalizationBusiness();
        $furs = $company->createFiscalizationService($business, $invoice);

        /**
         * Create invoice message and throw exception if something is not ok.
         * Invoice works.
         */
        $furs->createInvoiceMsg();
        $furs->postXml();
        $furs->getXmlResponse();
        echo $furs->getZoi();
    }

}