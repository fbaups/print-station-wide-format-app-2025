<?php

use Cake\Mailer\Transport\MailTransport;

return [
    'EmailTransport' => [
        //will be populated/overwritten from DB
        'default' => [
            'className' => 'Smtp',
            'host' => 'localhost',
            'port' => 2552,
            'username' => null,
            'password' => null,
            'client' => null,
            'tls' => false,
            'url' => env('EMAIL_TRANSPORT_DEFAULT_URL', null),
        ]
    ],
    'Email' => [
        //will be populated/overwritten from DB
        'default' => [
            'transport' => 'default',
            'from' => 'hello@localhost',
            'charset' => 'utf-8',
            'headerCharset' => 'utf-8',
        ]
    ]
];
