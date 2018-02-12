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

    public function getConfigureAction()
    {
        return view('Pckg/Pendo:pendo/configure');
    }

}