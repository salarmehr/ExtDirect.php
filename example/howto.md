Hello, how run the example ? ? ?

1. Download the "ExtDirect.php.zip" & Unzip the file

2. Copy the extracted files (ExtDirect.php, example.php ) to your web site folder (requires PHP and and HTTP, like Apache or Nginx)

3. Using your browser, go to the URL where you can access "http://yousite.com/extjs/example.php?javascript"

4. Create Empty ExtJS 6 application

5. Edit app.json, add code to "js" section

.....

    "js": [
        {
            "path": "app.js",
            "bundle": true
        }, {
            "path" : "http://yousite.com/extjs/example.php?javascript",
            "bundle": false,
            "remote": true
        }
    ]
    
....

6. All Done, now you can test Ext.Direct. Call Ext.php.Server.date() in you application for test, example down below

    Ext.php.Server.date( 'Y-m-d', function(result) {
        alert( 'Server date is ' + result ); } );
    };