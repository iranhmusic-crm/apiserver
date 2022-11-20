<?php

return [
  'adminEmail' => 'admin@example.com',
  'senderEmail' => 'noreply@example.com',
  'senderName' => 'Example.com mailer',

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
