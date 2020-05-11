<?php namespace Pckg\Pendo\Record;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Pckg\Database\Record;
use Pckg\Pendo\Entity\Companies;
use Pckg\Pendo\Service\Fiscalization\Business;
use Pckg\Pendo\Service\Fiscalization\Config;
use Pckg\Pendo\Service\Fiscalization\Invoice;
use Pckg\Pendo\Service\Fiscalization\Service\Furs;
use Pckg\Pendo\Service\Fiscalization\Service\Purh;

/**
 * Class Company
 *
 * @package Pckg\Pendo\Record
 *
 * @property null|integer id
 * @property string       business
 * @property string       device
 * @property string       vat_number
 */
class Company extends Record
{

    protected $entity = Companies::class;

    protected $toArray = ['country'];

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

    public function getFiscalizationConfig()
    {
        $key = $this->getDecodedPasswordAttribute();

        $certsPath = path('app_private') . 'company' . path('ds') . 'certificate' . path('ds');

        $url = $this->type == 'prod'
            ? 'https://blagajne.fu.gov.si:9003/v1/cash_registers'
            : 'https://blagajne-test.fu.gov.si:9002/v1/cash_registers';
        if (strtolower(substr($this->vat_number, 0, 2)) == 'hr') {
            $url = $this->type == 'prod'
                ? 'https://cis.porezna-uprava.hr:8449/FiskalizacijaService'
                : 'https://cistest.apis-it.hr:8449/FiskalizacijaServiceTest';
        }

        $vatNumber = $this->vat_number;
        if (strpos($vatNumber, 'HR') === 0 || strpos($vatNumber, 'SI') === 0) {
            $vatNumber = substr($vatNumber, 2);
        }

        return new Config(
            $vatNumber,
            $certsPath . $this->pem,
            $certsPath . $this->p12,
            $key,
            $certsPath . $this->server,
            config('derive.fiscalization.softwareSupplierTaxNumber'),
            $url
        );
    }

    public function getDecodedPasswordAttribute()
    {
        return $this->password && $this->hash
            ? Crypto::decrypt($this->password, Key::loadFromAsciiSafeString($this->hash))
            : null;
    }

    public function getInvisiblePasswordAttribute()
    {
        $pass = $this->getDecodedPasswordAttribute();

        return $pass
            ? substr($pass, 0, 1) . '******' . substr($pass, strlen($pass) - 1)
            : null;
    }

    public function createFiscalizationBusiness($business, $device)
    {
        return new Business(
            $business,
            $this->vat_number,
            date('Y-m-d', strtotime($this->incorporated_at)),
            $device
        );
    }

    public function createFiscalizationService(Business $business, Invoice $invoice)
    {
        return $this->country->createFiscalizationService($business, $invoice, $this);
    }

}