<?php
return [
    'discoverer' => [
        'paths' => [
            __DIR__ . '/../src'
        ]
    ],
    'api' => [
        'descriptor' => 'window.uERP_REMOTING_API',
        'declaration' => [
            'url' => 'localhost:8080/router.php',
            'type' => 'remoting',
            'id' => null,
            'namespace' => 'Ext.php',
            'timeout' => null
        ]
    ]
];