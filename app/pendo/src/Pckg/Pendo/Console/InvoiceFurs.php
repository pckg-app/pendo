<?php namespace Pckg\Pendo\Console;

use Pckg\Framework\Console\Command;
use Pckg\Pendo\Entity\Companies;
use Pckg\Pendo\Service\Fiscalization\Invoice;
use Symfony\Component\Console\Input\InputArgument;
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
                          ], InputOption::VALUE_REQUIRED)
             ->addArguments([
                                'price' => 'Fiscalization price',
                                'num'   => 'Invoice number',
                                'date'  => 'Invoice date',
                            ], InputArgument::REQUIRED);
    }

    public function handle()
    {
        $company = (new Companies())->where('id', $this->option('company'))->oneOrFail();
        $price = $this->argument('price');
        $num = $this->argument('num');
        $date = $this->argument('date');

        $invoice = new Invoice($num, $price, $price, $date);

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