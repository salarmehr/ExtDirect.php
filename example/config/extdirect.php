<?php
return [
    'discoverer' => [
        'paths' => [
            __DIR__ . '/../src'
        ]
    ],
    'cache' => [
        'directory' => __DIR__ . '/../cache',
        'lifetime' => 1800
    ],
    'api' => [
        'descriptor' => 'window.uERP_REMOTING_API',
        'declaration' => [
            'url' => 'localhost:8080/router.php',
            'type' => 'remoting',
            'id' => 'uERP', // it's required for the cache mechanism
            'namespace' => 'Ext.php',
            'timeout' => null
        ]
    ]
];