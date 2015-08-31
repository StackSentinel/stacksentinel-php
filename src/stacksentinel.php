<?php
//
// Welcome to the Stack Sentinel Error Notifier
// --------------------------------------------
//
// Replace the variables below with your values to get started with Stack Sentinel. Then in your module,
// call install_stack_sentinel() after including this file.
//
define('STACK_SENTINEL_ACCOUNT_TOKEN', "YOUR ACCOUNT TOKEN");
define('STACK_SENTINEL_PROJECT_TOKEN', "YOUR PROJECT TOKEN");

define('STACK_SENTINEL_ENVIRONMENT', "development");

define('STACK_SENTINEL_ENDPOINT', "https://api.stacksentinel.com/api/v1/insert");
 
// Our custom error handler
function stack_sentinel_error_handler($number, $message, $file, $line, $vars) {
    $traceback = array();

    foreach (array_reverse(debug_backtrace()) as &$value) {
        if ($value['function'] != 'stack_sentinel_error_handler') {
            $item = array();
            $item['module'] = $value['file'];
            $item['line'] = $value['line'];
            $item['method'] = $value['function'];
            array_push($traceback, $item);
        }
    }
    switch ($number) {
        case E_USER_ERROR:
            $type = "User Error";
            breka;
        case E_USER_WARNING:
            $type = "Warning";
            break;
        case E_USER_NOTICE:
            $type = "Notice";
            break;
        default:
            $type = "Programming Error";
            break;
    }

    $payload = array(
            "account_token" => STACK_SENTINEL_ACCOUNT_TOKEN,
            "project_token" => STACK_SENTINEL_PROJECT_TOKEN,
            "return_feedback_urls" => true,
            "errors" => array(
                    array(
                            "error_type" => "$type: $message",
                            "error_message" => $message,
                            "environment" => STACK_SENTINEL_ENVIRONMENT,
                            "traceback" => $traceback,
                            "state" => array(
                                    "vars" => $vars,
                                    "server" => $_SERVER,
                                    "env" => $_ENV,
                                    "headers" => getallheaders()
                                    ),
                            "tags" => array("php-test")
                            )
                    )
    );

    // create curl resource
    $ch = curl_init(STACK_SENTINEL_ENDPOINT);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
    ));

    // $output contains the output string
    $api_response = json_decode(curl_exec($ch));
    // close curl resource to free up system resources
    curl_close($ch); 

    if ( ($number !== E_NOTICE) && ($number < 2048) ) {
        die("<hr/>There was an error. We've been notified of it. Please try again later.");
    }
}

function install_stack_sentinel() {
    set_error_handler('stack_sentinel_error_handler');
}

?>


