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
use Pckg\Pendo\Service\Certificate;

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
        return '<pendo-configure-comms api-key="' . $appKey->key . '"></pendo-configure-comms>';
        //return view('Pckg/Pendo:pendo/configure', ['appKey' => $appKey, 'configureForm' => $configureForm]);
    }

    public function deleteUploadCertificateAction() {}

    public function postUploadCertificateAction(AppKey $appKey)
    {
        $upload = new Upload('file', ['application/x-pkcs12', 'application/octet-stream']);

        if (($message = $upload->validateUpload()) !== true) {
            return [
                'success' => false,
                'message' => $message,
            ];
        }

        $file = $upload->getFile()['name'];
        if (strpos($file, '.p12') !== (strlen($file) - 4)) {
            return [
                'success' => false,
                'message' => 'Upload .p12 file',
            ];
        }

        /**
         * We do not have a password, so we cannot validate it yet.
         */

        /**
         * Save certificate?
         */
        $uniqueName = uuid4();
        $final = $upload->save(path('private') . '/company/certificate/', $uniqueName);

        return [
            'success' => true,
            'url' => $final,
        ];
    }

    public function postValidateCertificateAction()
    {
        $validator = new Certificate();
        $status = $validator->getInfo($props, path('private') . '/company/certificate/', post('hash'), post('password'));
        return [
            'data' => $props,
            'status' => $status,
            'success' => $status === Certificate::CODE_SUCCESS
        ];
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
                'hash' => $asciiKey,
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