<?php namespace Pckg\Pendo\Controller;

use Pckg\Api\Record\AppKey;
use Pckg\Pendo\Form\Configure;

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
        dd($appKey->data(), $configure->getData(), $_FILES);

        /**
         * Here we accept certificates and password and store them securely.
         */
    }

}