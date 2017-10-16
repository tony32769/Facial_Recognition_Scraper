<?php

define('ERR_DBG', 'error');
define('INFO_DBG', 'info');
define('WARN_DBG', 'warning');
define('NOTICE_DBG', 'notice');
define('LOGGER_DBG', 'logger');

function debug($message = false, $type = INFO_DBG, $raw = false, $out_mode = false) {

    $log_directory = '/'; //@todo: make platform agnostic
    $file_name = 'debug.log';
    $debug_enable = true;
    $loggly_enable = false;
    $loggly_uid = '';
    $loggly_url = "http://logs-01.loggly.com/inputs/$loggly_uid/tag/http/";

    $log_file = "$log_directory$file_name";

    //set default loggin output if not set in call
    if($out_mode == false) $out_mode = 'stdout';
    ob_start();
    debug_print_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);
    $call = ob_get_contents();
    ob_end_clean();
    $call = trim($call, "\n");
    $call = substr($call, strpos($call, '['), strrpos($call, ']'));
    $call = trim($call, "[]");
    //$call = str_replace('/home/whmcsb/public_html', '.', $call);
    $call_exp = explode(':', $call);
    $func = $call_exp[0];
    $line = $call_exp[1];
    $output = '';
    if(!$debug_enable) return null;
    if ($message != false) {
        switch ($type) {

            case INFO_DBG:

                $output = "INFO :: ";
                break;

            case ERR_DBG:
                $output = "ERROR :: ";
                break;

            case WARN_DBG:
                $output = "WARNING :: ";
                break;

            case NOTICE_DBG:
                $output = "NOTICE :: ";
                break;
            case LOGGER_DBG:
                $output = "LOGGER :: ";
                break;
            default:
                debug("Unexpected message type :: Type: $type , Message: $message", LOGGER_DBG);
                return;
        }


        //build the output string for usage throughout the rest of the prog output
        $output .= "Fn:[$func], Ln:[$line] :: Msg: $message\n";

        //use standard variable output, raw output if raw enabled
        if (!$raw) {
            switch ($out_mode) {
                case 'stdout':
                    fwrite(STDOUT, $output);
                    break;

                case 'echo':
                    echo($output);
                    break;

                case 'html':
                    echo '<p>' . $output . '</p>';
                    break;

                case 'loggly':
                    $headers = array(
                        'Content-Type: application/x-www-form-urlencoded'
                    );

                    $data = array(
                        'message' => $message,
                        'type' => $type,
                        'function' => $func,
                        'line' => $line,
                        'timestamp' => time()
                    );

                    doPost($loggly_url, $data, $headers);
                    break;
				case 'file':
					file_put_contents($log_file, $output, FILE_APPEND);
					break;
					
                default:
                    echo($output);
            }
        } else {
            //raw mode, bypass error type and output raw variable data; This should follow an actual error message
            switch ($out_mode) {
                case 'stdout':
                    fwrite(STDOUT, var_export($message, true) . "\n");
                    break;

                case 'echo':
                    echo(var_export($message, true));
                    break;

                case 'html':
                    echo '<pre>';
                    print_r($message, true);
                    echo '</pre>';
                    break;
				case 'file':
					file_put_contents($log_file, var_export($message, true), FILE_APPEND);
					break;

                default:
                    echo(var_export($message, true));
            }


        }

        if($loggly_enable){
            $headers = array(
                'Content-Type: application/x-www-form-urlencoded'
            );

            $data = array(
                'message' => $message,
                'type' => $type,
                'function' => $func,
                'line' => $line,
                'timestamp' => time()
            );

            doPost($loggly_url, $data, $headers);
        }
    } else {
        debug('No error message was given', 'logger');
    }
}

//url is a string //data is an array k/v
function doPost($url, $data, $headers = false) {

    //create field string from array
    $fields = http_build_query($data);

    //setup curl
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if($headers != false && is_array($headers)){
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    //run curl request
    $server_output = curl_exec ($ch);
    //close curl connection
    curl_close ($ch);

    //return response data to calling function
    return $server_output;
}

function dbgToFile($message, $type = INFO_DBG){
    debug($message, $type, false, 'file');
}