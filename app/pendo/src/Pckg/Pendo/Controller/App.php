<?php namespace Pckg\Pendo\Controller;

use Pckg\Pendo\Form\RegisterApp;

class App
{

    public function postRegisterAction(RegisterApp $registerApp)
    {
        $appData = $registerApp->getData();

        $app = \Pckg\Pendo\Record\App::getOrCreate(only($appData, ['user_id', 'company_id']), null, only($appData, ['title']));

        return [
            'success' => true,
            'app' => $app,
        ];
    }

}