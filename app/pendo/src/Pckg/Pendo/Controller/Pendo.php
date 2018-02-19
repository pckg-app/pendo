<?php namespace Pckg\Pendo\Controller;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Pckg\Manager\Upload;
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

}