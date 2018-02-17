<?php namespace Pckg\Pendo\Controller;

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

    public function getConfigureAction($apiKey = null)
    {
        return view('Pckg/Pendo:pendo/configure', ['apiKey' => $apiKey]);
    }

    public function postConfigureAction()
    {
        /**
         * Here we accept certificates and password and store them securely.
         */
    }

}