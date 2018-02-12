<?php

use Pckg\Auth\Entity\Users;
use Pckg\Auth\Service\Auth;
use Pckg\Auth\Service\Provider\Database;

return [
    'gates'        => [
        /**
         * Restrict access for admin tag.
         */
        [
            'provider' => 'frontend',
            'tags'     => ['auth:in'],
            'internal' => 'login',
        ],
        [
            'provider' => 'frontend',
            'tags'     => ['group:admin'],
            'internal' => 'derive.user.profile',
        ],
        [
            'provider' => 'frontend',
            'tags'     => ['group:checkin'],
            'internal' => 'derive.user.profile',
        ],
        [
            'provider' => 'frontend',
            'tags'     => ['auth:out'],
            'redirect' => 'homepage',
        ],
    ],
    'tags'         => [
        'group:admin'   => function(Auth $auth) {
            return $auth->isAdmin();
        },
        'group:checkin' => function(Auth $auth) {
            return $auth->getGroupId() == 5 || $auth->isAdmin();
        },
        'auth:in'       => function(Auth $auth) {
            return $auth->isLoggedIn();
        },
        'auth:out'      => function(Auth $auth) {
            return !$auth->isLoggedIn();
        },
    ],
    'providers'    => [
        'frontend' => [
            'type'           => Database::class,
            'entity'         => Users::class,
            'hash'           => '',
            'version'        => 'secure',
            'forgotPassword' => true,
            'userGroup'      => 'status_id',
        ],
    ],
    'provider'     => [
        'facebook' => [
            'config' => [
                'app_id'     => '',
                'app_secret' => '',
            ],
        ],
    ],
    'getParameter' => 'autologin',
];
