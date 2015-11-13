# extdirect

An PHP implementation of the Sencha Ext Direct Specification

[This project has some code from J. Bruni (ExtDirect.php)](http://www.sencha.com/forum/showthread.php?102357-Extremely-Easy-Ext.Direct-integration-with-PHP)

## Install
    
    composer require danielbragaalmeida/extdirect

## How to Use

### PHP 

#### config.php

    <?php
    return [
        'discoverer' => [
            'paths' => [ // Directories of your classes
                __DIR__ . '/src', 
            ]
        ],
        'cache' => [
            'directory' => __DIR__ . '/cache',
            'lifetime' => 60,
        ],
        'cors' => [
            'requestAllowedOrigins' => [ // A list of domains that can consume your API 
                'http://www.myadomain.com' => true,
            ],
            'requestAllowedMethods' => [
                'GET' => true,
                'OPTIONS' => true,
                'POST' => true
            ],
            'requestAllowedHeaders' => [
                'content-type'     => true,
                'x-requested-with' => true,
                'origin'           => true,
            ],
            'requestCredentialsSupported' => true,
            'checkHost' => true,
            'serverOrigin' => [
                'scheme' => 'http',
                'host'   => 'localhost',
                'port'   => '8081',
            ]
        ],
        'api' => [
            'descriptor' => 'window.uERP_REMOTING_API',
            'declaration' => [
                'url' => 'http://api.myadomain.com/router.php', // Your router may be in another domain
                'type' => 'remoting',
                'id' => 'uERP', // it's required for the cache mechanism
                'namespace' => 'Ext.php',
                'timeout' => null,
            ]
        ]
    ];
    
### Sample class src/Server.php

    <?php
    namespace Util;
    
    /**
     * Class Server
     *
     * @ExtDirect
     * @ExtDirect\Alias UtilServer
     */
    class Server
    {
        /**
         * @param $format
         * @return bool|string
         * @ExtDirect
         */
        public function date($format)
        {
            return date($format);
        }
    
        /**
         *
         * @return string
         * @ExtDirect
         */
        public function hostname()
        {
            return gethostname();
        }
    }

#### api.php

    <?php
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $config = new ExtDirect\Config(include 'config.php');
    
    $discoverer = new ExtDirect\Discoverer($config);
    $discoverer->start();
    
#### router.php

    <?php
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $config = new ExtDirect\Config(include 'config.php');
    
    $discoverer = new ExtDirect\Router($config);
    $discoverer->route();

### HTML
    <-- API in same domain -->
    <script type="text/javascript" src="api.php"></script>
    
    <-- API in another domain -->
    <script type="text/javascript" src="http://api.mydomain.com/api.php"></script>

### JavaScript:

Here, you can call actions/methods from your API. If you exposed the `Util\Server` (alias `UtilServer`) class and the `date` method, You must
  class the API as follows:

    Ext.Direct.addProvider(window.uERP_REMOTING_API); // window.uERP_REMOTING_API is your descriptor
    Ext.php.UtilServer.date('Y-m-d', function(result) {
        alert('Server date is ' + result); 
    });

## Features

1.  Configure once, work everywhere.
    
    You configure the path where your classes resides, not which classes you will expose.

2.  Use of Annotations to easily determine which classes and methods are exposed. 
    All classes with `@ExtDirect` will be inspected for methods that can be exposed (only methods com `@ExtDirect` will
    be exposed).

3.  Cache mechanism. Your API classes/methods will be cached to avoid overloading the discovery process. You can configure
    the cache lifetime.
    
4.  [CORS support](http://enable-cors.org). You can host your API in a server other than your web application server.

## Configuration
An array with the following structure:
    
-   discovery 
    -   paths `array` 
-   cache `array`
    -   directory `string` 
    -   lifetime `int`
-   cors
    -   requestAllowedOrigins `array`
    -   requestAllowedMethods `array`
    -   requestAllowedHeaders `array`
    -   requestCredentialsSupported `boolean`
    -   checkHost `boolean`
    -   serverOrigin
        -   scheme `string` 
        -   host `string` 
        -   port `string` 
-   api
    -   descriptor `string` 
    -   declaration
        -   url `string` 
        -   type `string` 
        -   id `string` 
        -   namespace `string` 
        -   timeout `int` 

### Discovery config

**discovery.paths:** An array with paths to your classes.

    <?php
    ...
    'discoverer' => [
        'paths' => [
            __DIR__ . '/../src',
            __DIR__ . '/../lib',
        ]
    ],
    ...

### Discovery config

**cache.directory:** The directory that will be used to store the cached data.

**cache.lifetime:** The cache lifetime, in seconds.

    <?php
    ...
    'cache' => [
        'directory' => __DIR__ . '/../cache',
        'lifetime' => 60,
    ],
    ...
    
### CORS config

**cors.requestAllowedOrigins:** A list of allowed request origins (lower-cased, no trail slashes). 
Value `true` enables and value `null` disables origin. 
If all origins '*' are enabled all settings for other origins are ignored. 

**cors.requestAllowedMethods:** A list of allowed request methods (case sensitive). Value `true` enables and value `null` disables method.

**cors.requestAllowedHeaders:** A list of allowed request headers (lower-cased). Value `true` enables and value `null` disables header.

**cors.requestCredentialsSupported:** If access with credentials is supported by the resource.

**cors.checkHost:** If request 'Host' header should be checked against server's origin.

**cors.serverOrigin.scheme:** Used to compare the Host header of request, if checkHost is enabled.

**cors.serverOrigin.host:** Used to compare the Host header of request, if checkHost is enabled.

**cors.serverOrigin.port:** Used to compare the Host header of request, if checkHost is enabled.

    <?php
    ...
    'cors' => [
        'requestAllowedOrigins' => [
            'http://example.com:123' => true,
            'http://evil.com'        => null,
            '*'                      => null,
        ],
        'requestAllowedMethods' => [
            'GET' => true,
            'OPTIONS' => true,
            'POST' => true
        ],
        'requestAllowedHeaders' => [
            'content-type'     => true,
            'x-requested-with' => true,
            'origin'           => true,
        ],
        'requestCredentialsSupported' => true,
        'serverOrigin' => [
            'scheme' => 'http',
            'host'   => 'localhost',
            'port'   => '8081',
        ]
    ],
    ...

**api.descriptor:** The JavaScript variable which will receive the API declaration.

**api.declaration.url:** The Service URI for this API.

**api.declaration.type:** MUST be either remoting for Remoting API, or polling for Polling API.

**api.declaration.id:**  The identifier for the Remoting API Provider. This is useful when there are more than one API in use.
Cache mechanism require it.

**api.declaration.namespace:** The Namespace for the given Remoting API.

**api.declaration.timeout:** The number of milliseconds to use as the timeout for every Method invocation in this Remoting API.
(not implemented)


## TODO

1. Handles forms
2. Handles file uploads
 