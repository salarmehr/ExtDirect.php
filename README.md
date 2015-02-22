# ExtDirect.php
Extremely Easy Ext.Direct integration with PHP


[Created by: J. Bruni](http://www.sencha.com/forum/showthread.php?102357-Extremely-Easy-Ext.Direct-integration-with-PHP)


How to use:
===========

1) PHP

PHP Code:

    <?php
    
    require 'ExtDirect.php';
    
    class Server
    {
        public function date( $format )
        {
            return date( $format );
        }
    }
    
    ExtDirect::provide( 'Server' );
    
    ?>

Here, "Server" is the PHP class that we want to provide access from the JavaScript code. It could be any other class.

2) HTML:

Code:

    <script type="text/javascript" src="ext-direct.php?javascript"></script>

Here, "ext-direct.php" points to the PHP file shown on item 1. The "?javascript" query string is necessary, because the default output is on JSON format (good for Ext Designer).

3) JavaScript:

Code:

    Ext.php.Server.date( 'Y-m-d', function(result){ alert( 'Server date is ' + result ); } );

Here, to call the "date" method from PHP "Server" class, we prepended the default namespace Ext.php. The first parameter is the $format parameter. The second parameter is the JavaScript callback function that will be executed after the AJAX call has been completed. Here, an alert box shows the result.



What are you waiting for the download?
--------------------------------------

It includes:

- ExtDirect.php - This is the file you include in your PHP script.
-   example.php - This is a working sample (PHP part). example.html - The
-   HTML and JavaScript parts of the working sample.

Features
--------
- API declaration with several classes (not limited to a single class)
- API "url", "namespace" and "descriptor" settings ("ExtDirect" class assigns them automatically if you don't)
- Two types of API output format: "json" (for use with Ext Designer) and "javascript" (default: json)
- You choose if the "len" attribute of the actions will count only the required parameters of the PHP method, or all of them (default: all)
- You choose whether inherited methods will be declared in the API or not (default: no)
- You choose whether static methods will be declared in the API or not (default: no)
- Instantiate an object if the called method is static? You choose! (default: no)
- Call the class constructor with the actions parameters? You choose! (default: no)
- "debug" option to enable server exceptions to be sent in the output of API action results (default: off)
- "utf8_encode" option to automatically apply UTF8 encoding in API action results (default: off)
- Handles forms
- Handles file uploads
Configuration - How To
----------------------
Easy.
If the configuration option name is "configuration_name" and the configuration value is $value, use this syntax:

    ExtDirect::$configuration_name = $value;  

And that's all.

Now, let's see the available configuration options:

- name: api_classes
- type: array of strings
- meaning: Name of the classes to be published to the Ext.Direct API
- default: empty
- comments: This option is overriden if you provide a non-empty $api_classes parameter for the "ExtDirect::provide" method. Choose one or another. If you want to declare a single class, you can set $api_classes as a string, instead of an array.
- example
PHP Code:

     ExtDirect::$api_classes = array( 'Server', 'OtherClass',  'StoreProvider' );  

- name: url
- type: string
- meaning: Ext.Direct API attribute "url"
- default: $_SERVER['PHP_SELF']
- comments: Sometimes, PHP_SELF is not what we want. So, it is possible to specify the API URL manually.
- example
PHP Code:
    
    ExtDirect::$url = '/path/to/my_php_script.php';  

    
- name: namespace
- type: string
- meaning: Ext.Direct API attribute "namespace"
- default: "Ext.php"
- comments: Feel free to choose your own namespace, according to ExtJS rules for it.
- example
PHP Code:
ExtDirect::$namespace = 'Ext.Dharma';  
- name: descriptor
- type: string
- meaning: Ext.Direct API attribute "descriptor"
- default: "Ext.php.REMOTING_API"
- comments: Feel free to choose your own descriptor, according to ExtJS rules for it, and to the chosen namespace.
- example
PHP Code:
ExtDirect::$descriptor = 'Ext.Dharma.REMOTING_API';  
- name: count_only_required_params
- type: boolean
- meaning: Set this to true to count only the required parameters of a method for the API "len" attribute
- default: false
- example
PHP Code:
ExtDirect::$count_only_required_params = true;  
- name: include_static_methods
- type: boolean
- meaning: Set this to true to include static methods in the API declaration
- default: false
- example
PHP Code:
ExtDirect::$include_static_methods = true;  
- name: include_inherited_methods
- type: boolean
- meaning: Set this to true to include inherited methods in the API declaration
- default: false
- example
PHP Code:
ExtDirect::$include_inherited_methods = true;  
- name: instantiate_static
- type: boolean
- meaning: Set this to true to create an object instance of a class even if the method being called is static
- default: false
- example
PHP Code:
ExtDirect::$instantiate_static = true;  
- name: constructor_send_params
- type: boolean
- meaning: Set this to true to call the action class constructor sending the action parameters to it
- default: false
- example
PHP Code:
ExtDirect::$constructor_send_params = true;  
- name: debug
- type: boolean
- meaning: Set this to true to allow exception detailed information in the output
- default: false
- example
PHP Code:
ExtDirect::$debug = true;  
- name: utf8_encode
- type: boolean
- meaning: Set this to true to pass all action method call results through utf8_encode function
- default: false
- example
PHP Code:
ExtDirect::$utf8_encode = true;  
- name: default_api_output
- type: string
- meaning: API output format - available options are "json" (good for Ext Designer) and "javascript"
- default: "json"
- comments: Another way to enforce "javascript" output is to append the "?javascript" query string in the end of your PHP script URL; do this in the HTML `<script>` tag that refers to your API
example
PHP Code:
ExtDirect::$default_api_output = "javascript";  


formHandler - How-To
====================

There are two different ways to flag a method as a "formHandler".


Method 1: use the new ExtDirect::$form_handlers configuration option.

- name: form_handlers
- type: array of strings
- meaning: Name of the class/methods to be flagged as formHandler in the Ext.Direct API
- default: empty
- comments: The string format for each method must be "className::methodName".
- example 

PHP Code:
ExtDirect::$form_handlers = array( 'someClass::someMethod', 'Server::date' );  
Method 2: include "@formHandler" in the DOC comment of the method.

Example:

      class FTP_Manager
      {
          /**
           * Sets FTP password for a specific account
           * 
           * @formHandler
           * @param string $account   Name of the account
           * @param string $password   New password
           * @param string $password_confirm   New password confirmation
           * @return string
           */
          public function set_ftp_password( $account, $password, $password_confirm )
          {
              // do stuff
              return $result;
          }
      }  
      
In the example above, due to the "@formHandler" string inside the method's DOC comment, it will be flagged as a "formHandler" method.

It has the same effect as this:

PHP Code:
ExtDirect::$form_handlers[] = 'FTP_Manager::set_ftp_password';  
* Receiving parameters *

The parameters sent by forms are adapted to be received by the class method.

Pay attention now, because this is not usual.

I will use the "set_ftp_password" method above as the example.

First, note that we don't want that all formHandler methods have the same not-friendly signature, like this:

PHP Code:

        function set_ftp_password( $data );
        function do_something( $data );
        function do_something_completely_different( $data );  

Where $data is the user input (usually $_POST)

So, to be able to keep normal method signatures, like this...

PHP Code:
function set_ftp_password( $account, $password, $password_confirm )  
...I have implemented the following solution:

When the method/action function is a formHandler, its parameter values are taken from the input names that matches the parameter's names.

So... $_POST['account'] will automatically become the $account parameter...

$_POST['password'] value will be the $password parameter value...

...and from where the $password_confirm parameter value will come from? Yes! From $_POST['password_confirm']

That's it: the method's parameters' names matches the $_POST array keys.

Advantages:

- Don't need to worry with parameter order
- Can use meaningful and clean method/function signature
- Don't need to sniff with $_POST array - the ExtDirect controller does this for us (forget "isset" checkings... if a certain parameter value is not set in the $_POST array, the default value - if available - or null is passed to the method/function)

Disadvantages:

- The input names must match the method/function parameter names (IMHO, an advantage!)
- This approach may just not be the best for you (in this case, just ignore all this stuff and go for the $_POST / $_GET / $_REQUEST arrays!)

Of course, all validation / filtering / sanitization of input data, as always, must be carefully considered.


Additional note about file upload:

If your file input name is "userfile", it will be available in your method/function parameter named $userfile

In other words: your $userfile parameter will receive the value from $_FILES['userfile']

configuration options
---------------------

"declare_method_function", "authorization_function", "transform_result_function", and "transform_response_function"

Now, you are able to specify an "authorization_function", where you can check the user permissions and return true or false accordingly, allowing the API call or not.

With the "transform_result_function", you can modify the result of the API method call after its execution, but before it is sent to the client-side.

Finally, with the "transform_response_function", you can modify the response structure. This allows to fire server-side events, and to send extra server side data together with the RPC result.

    // New configuration option
    ExtDirect::$id = 'my_api';
    
    // All "function" configurations accept parameters of callback type
    ExtDirect::$transform_result_function   = 'transform_result';
    ExtDirect::$transform_response_function = 'transform_response';
    ExtDirect::$declare_method_function     = 'declare_method';
    ExtDirect::$authorization_function      = 'authorize';
    
        function transform_result( $action, $result )
        {
            if ( $action->form_handler )
                $result = array( 'success' => $result );
            
            return $result;
            // return modified result
        }
        
        function transform_response( $action, $response )
        {
            $response['error_msg']   = MyFramework::$errors;
            $response['success_msg'] = MyFramework::$success;
            return $response;
            // return modified response
        }
        
        function declare_method( $class, $method )
        {
            $key = $class . '::' . $method;
            return in_array( $key, MyFramework::$user->permissions );
            // return boolean - declare the method in the API or not
        }
        
        function authorize( $action )
        {
            return declare_method( $action->action, $action->method );
            // return boolean - authorize the action call or not
        }
    
    // Types of the parameters received by the functions are:
    // $action - ExtDirectAction
    // $result - array
    // $response - array
    // $class - string
    // $method - string  