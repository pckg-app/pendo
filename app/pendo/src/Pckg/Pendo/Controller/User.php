<?php namespace Pckg\Pendo\Controller;

use Pckg\Pendo\Form\RegisterUser;

class User
{

    public function postRegisterAction(RegisterUser $registerUser)
    {
        $data = $registerUser->getData();
        $user = \Pckg\Auth\Record\User::getOrCreate(['email' => $data['email']], null, ['user_group_id' => 1]);

        return [
            'success' => true,
            'user' => only($user, ['id', 'email']),
        ];
    }

}