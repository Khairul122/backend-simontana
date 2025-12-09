<?php

return [
    /*
    |--------------------------------------------------------------------------
    | JWT Secret Key
    |--------------------------------------------------------------------------
    |
    | Don't ever expose this secret key in public. This key is used for
    | encoding and decoding JWT tokens.
    |
    */
    'secret' => env('JWT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | JWT Time To Live
    |--------------------------------------------------------------------------
    |
    | This value determines the number of minutes that a JWT token will be
    | valid. You can set this to whatever you wish.
    |
    */
    'ttl' => env('JWT_TTL', 60),

    /*
    |--------------------------------------------------------------------------
    | JWT Refresh Time To Live
    |--------------------------------------------------------------------------
    |
    | This value determines the number of minutes that a refresh token will be
    | valid. You can set this to whatever you wish.
    |
    */
    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),

    /*
    |--------------------------------------------------------------------------
    | JWT Algorithm
    |--------------------------------------------------------------------------
    |
    | This value determines the algorithm that will be used to sign the JWT.
    | Supported algorithms: 'HS256', 'HS384', 'HS512', 'RS256', 'RS384', 'RS512'
    |
    */
    'algo' => 'HS256',
];