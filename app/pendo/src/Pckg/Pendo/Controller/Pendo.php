<?php namespace Pckg\Pendo\Controller;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Pckg\Manager\Upload;
use Pckg\Pendo\Console\EchoFurs;
use Pckg\Pendo\Console\EchoPurh;
use Pckg\Pendo\Console\InvoiceFurs;
use Pckg\Pendo\Console\InvoicePurh;
use Pckg\Pendo\Entity\Fiscalizations;
use Pckg\Pendo\Form\Configure;
use Pckg\Pendo\Record\AppKey;

/**
 * Class Pendo
 *
 * @package Pckg\Pendo\Controller
 */
class Pendo
{

    /**
     * @return string
     */
    public function getIndexAction()
    {
        return view('Pckg/Pendo:pendo/index');
    }

    public function getConfigureAction(AppKey $appKey, Configure $configureForm)
    {
        return view('Pckg/Pendo:pendo/configure', ['appKey' => $appKey, 'configureForm' => $configureForm]);
    }

    public function postConfigureAction(AppKey $appKey, Configure $configure)
    {
        /**
         * Here we accept certificates and password and store them securely.
         */
        $key = Key::createNewRandomKey();
        $asciiKey = $key->saveToAsciiSafeString();

        $files = ['p12', 'pem', 'server'];
        $data = post('password')
            ? [
                'password' => Crypto::encrypt(post('password'), $key),
                'hash'     => $asciiKey,
            ]
            : [];
        $uploads = [];
        foreach ($files as $file) {
            $uploads[$file] = $upload = new Upload($file);
            $success = $upload->validateUpload();

            if ($success !== true) {
                continue;
            }

            $dir = path('app_private') . 'company' . path('ds') . 'certificate' . path('ds');

            $upload->save($dir);

            $data[$file] = $upload->getUploadedFilename();
        }

        $appKey->app->company->setAndSave($data);

        return response()->respondWithSuccessRedirect();
    }

    public function getFiscalizationsAction(AppKey $appKey)
    {
        $fiscalizations = (new Fiscalizations())->where('business_tax_number', substr($appKey->app->company->vat_number, 2))
                                                ->limit(500)
                                                ->orderBy('id DESC')
                                                ->all();

        return ['fiscalizations' => $fiscalizations];
    }

    public function getCheckAction()
    {
        echo "<h1>PURH</h1><br />\n";
        echo "<h2>Echo</h2><br />\n";
        (new EchoPurh())->executeManually(['--company' => 3]);
        echo "<h2>Invoice</h2><br />\n";
        (new InvoicePurh())->executeManually(['--company' => 3]);

        echo "<h1>FURS</h1><br />\n";
        echo "<h2>Echo</h2><br />\n";
        (new EchoFurs())->executeManually(['--company' => 4]);
        echo "<h2>Invoice</h2><br />\n";
        (new InvoiceFurs())->executeManually(['--company' => 4]);

        return response()->respond('ok');
    }

}