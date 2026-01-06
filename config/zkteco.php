<?php

return [
    'device_ip' => env('ZKTeco_DEVICE_IP', '192.168.1.100'),
    'device_port' => env('ZKTeco_DEVICE_PORT', 4370),
    'device_password' => env('ZKTeco_DEVICE_PASSWORD', null),
    'timeout' => env('ZKTeco_TIMEOUT', 10),
    'should_ping' => env('ZKTeco_SHOULD_PING', true),
];