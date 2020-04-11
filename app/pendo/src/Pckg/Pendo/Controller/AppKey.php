<?php namespace Pckg\Pendo\Controller;

use Pckg\Pendo\Form\RegisterAppKey;

class AppKey
{

    public function postRegisterAction(RegisterAppKey $registerAppKey)
    {
        $appKeyData = $registerAppKey->getData();

        $appKey = \Pckg\Pendo\Record\AppKey::getOrCreate(only($appKeyData, ['app_id']), null, ['valid' => true, 'key' => sha1(microtime())]);

        return [
            'success' => true,
            'appKey' => $appKey,
        ];
    }

    public function getAppKeyAction(\Pckg\Pendo\Record\AppKey $appKey)
    {
        return [
            'company' => only($appKey->app->company, ['id', 'country', 'vat_number', 'business_number', 'short_name']),
            'user' => $appKey->app->user,
            'certificate' => [
                'invisiblePassword' => $appKey->app->company->getInvisiblePasswordAttribute(),
            ]
        ];
    }

}