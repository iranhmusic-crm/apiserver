<?php

return [
  'adminEmail'  => 'must be define in local file',
  'senderEmail' => 'must be define in local file',
  'senderName'  => 'must be define in local file',

  'settings' => [
    'AAA' => [
      'approvalRequest' => [
        'email' => [
          'resend-ttl' => 2 * 60, //2 minutes
          'expire-ttl' => 2 * 24 * 3600, //2 days
        ],
        'mobile' => [
          'resend-ttl' =>  2 * 60, // 2 minutes
          'expire-ttl' => 15 * 60, //15 minutes
        ],
      ],
      'forgotPasswordRequest' => [
        'email' => [
          'resend-ttl' => 2 * 60, //2 minutes
          'expire-ttl' => 2 * 24 * 3600, //2 days
        ],
        'mobile' => [
          'resend-ttl' =>  2 * 60, // 2 minutes
          'expire-ttl' => 15 * 60, //15 minutes
        ],
      ],
      // 'password' => [
      //   'age' => 0, //never expire
      //   'min-length' => 3,
      // ],
      'jwt' => [
        'ttl' => 5 * 60, //5 minutes
      ],
    ],
  ],
];
