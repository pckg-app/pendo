<?php namespace Pckg\Pendo\Record;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Exception;
use Pckg\Database\Record;
use Pckg\Pendo\Entity\Companies;
use Pckg\Pendo\Service\Fiscalization\Business;
use Pckg\Pendo\Service\Fiscalization\Config;
use Pckg\Pendo\Service\Fiscalization\Service\Furs;
use Pckg\Pendo\Service\Fiscalization\Service\Purh;
use Throwable;

class Company extends Record
{

    protected $entity = Companies::class;

    public function getCertificatePassword()
    {
        try {
            return Crypto::decrypt($this->cert_password, Key::loadFromAsciiSafeString(config('security.key')));
        } catch (Throwable $e) {
            throw new Exception('Error decrypting certificate key', null, $e);
        }
    }

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
        $key = $this->getCertificatePassword();

        $certsPath = path('app_private') . 'certs' . path('ds');

        return new Config(
            $this->vat_number,
            $certsPath . $this->pem_cert,
            $certsPath . $this->p12_cert,
            $key,
            $certsPath . $this->server_cert,
            config('derive.fiscalization.softwareSupplierTaxNumber')
        );
    }

    public function getDecodedPasswordAttribute()
    {
        return Crypto::decrypt($this->password, Key::loadFromAsciiSafeString($this->hash));
    }

}