<?php namespace Pckg\Pendo\Console;

use Pckg\Framework\Console\Command;
use Pckg\Pendo\Entity\Companies;
use Pckg\Pendo\Record\Company;
use Pckg\Pendo\Service\Certificate;
use Pckg\Pendo\Service\Fiscalization\Invoice;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class DebugCert
 *
 * @package Pckg\Pendo\Console
 */
class DebugCert extends Command
{

    protected function configure()
    {
        $this->setName('cert:debug')
            ->setDescription('Debug certificate')
            ->addArguments([
                'file' => 'Certificate',
            ], InputArgument::REQUIRED)
            ->addArguments([
                'pass' => 'Password',
            ], InputArgument::OPTIONAL)
            ->addOptions([
                'regenerate' => 'Regenerate .pem'
            ], InputOption::VALUE_NONE)
            ->addOptions([
                'company' => 'Use company instead of password'
            ], InputOption::VALUE_REQUIRED);
    }

    public function handle()
    {
        /*$companies = (new Companies())->all();
        foreach ($companies as $company) {
            d($company->vat_number, $company->decodedPassword, $company->p12, "");
        }*/

        $file = $this->argument('file');
        $path = path('private') . 'company/certificate/';
        $pass = $this->argument('pass');

        if ($company = $this->option('company')) {
            $pass = Company::getOrFail($company)->decodedPassword;
        }

        $status = (new Certificate())->getInfo($props, $path, $file, $pass);
        if ($status !== Certificate::CODE_SUCCESS) {
            $this->output($status, 'error');
        }

        if (!$this->option('regenerate')) {
            return;
        }

        $this->outputDated('Regenerating');
        $ok = (new Certificate())->makePemFromP12($path . $file, $pass);
        $this->outputDated('Regenerated: ' . $ok);
    }

}