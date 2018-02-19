<?php namespace Pckg\Pendo\Record;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Exception;
use Pckg\Database\Record;
use Pckg\Pendo\Entity\Companies;
use Pckg\Pendo\Service\Fiscalization\Business;
use Pckg\Pendo\Service\Fiscalization\Config;
use Pckg\Pendo\Service\Fiscalization\Invoice;
use Pckg\Pendo\Service\Fiscalization\Service\Furs;
use Pckg\Pendo\Service\Fiscalization\Service\Purh;
use Throwable;

class Company extends Record
{

    protected $entity = Companies::class;

    public function getCountryCode()
    {
        return strtolower(substr($this->vat_number, 0, 2));
    }

    public function getFiscalizationHandler(Config $config, Business $business)
    {
        $code = $this->getCountryCode();

        if ($code == 'sl') {
            return new Furs($config, $business);
        } else {
            return new Purh($config, $business);
        }
    }

    public function getFisclizationConfig()
    {
        $key = $this->getDecodedPasswordAttribute();

        $certsPath = path('app_private') . 'certs' . path('ds');

        return new Config(
            $this->vat_number,
            $certsPath . $this->pem,
            $certsPath . $this->p12,
            $key,
            $certsPath . $this->server,
            config('derive.fiscalization.softwareSupplierTaxNumber')
        );
    }

    public function getDecodedPasswordAttribute()
    {
        return Crypto::decrypt($this->password, Key::loadFromAsciiSafeString($this->hash));
    }

    public function getInvisiblePasswordAttribute()
    {
        $pass = $this->getDecodedPasswordAttribute();

        return substr($pass, 0, 1) . '******' . substr($pass, strlen($pass) - 1);
    }

    public function createFiscalizationBusiness()
    {
        return new Business(
            $this->fiscalization_business ?? 'PP2',
            substr($this->vat_number, 2), // remove SI, HR from starting of vat number
            date('Y-m-d', strtotime($this->incorporated_at)),
            $this->fiscalization_device ?? 1
        );
    }

    public function createFiscalizationService(Business $business, Invoice $invoice)
    {
        return $this->country->createFiscalizationService($business, $invoice, $this);
    }

}