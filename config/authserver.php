<?php

return [
    'client_id' => env('AUTH_SERVER_CLIENT_ID', null),
    'token' => env('AUTH_SERVER_TOKEN', null),
    'public_key' => env('AUTH_SERVER_PUBLIC_KEY_FILE', 'auth-server-public.key'),
];
