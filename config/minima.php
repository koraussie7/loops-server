<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Minima Blockchain RPC Configuration
    |--------------------------------------------------------------------------
    |
    | Connection settings for the Minima blockchain RPC (port 9005).
    | Used by MinimaRpcService to send DADA AI token rewards.
    |
    */

    'rpc' => [
        'host' => env('MINIMA_RPC_HOST', '185.55.240.110'),
        'port' => env('MINIMA_RPC_PORT', 9005),
        'password' => env('MINIMA_RPC_PASSWORD', 'privseairpc'),
        'ssl' => env('MINIMA_RPC_SSL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | DADA AI Token
    |--------------------------------------------------------------------------
    |
    | The token ID for DADA AI on the Minima chain.
    |
    */

    'dada_tokenid' => env('DADA_AI_TOKENID', '0x9A9A8FCCA41541FF5678EBF1648736E8E58CFCFC120A33006DAB7A233DCB7D0F'),

    /*
    |--------------------------------------------------------------------------
    | Video Reward Settings
    |--------------------------------------------------------------------------
    |
    | Reward rate and limits for video watch-to-earn.
    |
    */

    'reward' => [
        'per_second' => (int) env('DADA_REWARD_PER_SECOND', 10),
        'min_watch_seconds' => (int) env('DADA_MIN_WATCH_SECONDS', 30),
        'max_per_session' => (int) env('DADA_MAX_PER_SESSION', 100000),
        'daily_max' => (int) env('DADA_DAILY_MAX', 1000),
    ],
];
