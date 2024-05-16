<?php
// return [
//     'paths' => ['*'],
//     'allowed_methods' => ['*'],
//     'allowed_origins' => ['https://rapisurv2-k6dl.vercel.app'], //add your allowed origins
//     'allowed_origins_patterns' => [],
//     'allowed_headers' => ['*'],
//     'exposed_headers' => [],
//     'max_age' => 0,
//     'supports_credentials' => false,
// ];

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['https://rapisurv2-k6dl.vercel.app'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => false,

    'max_age' => false,

    'supports_credentials' => false,

];