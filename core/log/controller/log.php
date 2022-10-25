<?php

namespace core\log\controller;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;
use core\config\controller\config;

/**
 * Handles log files
 *
 * @author Dani Gilabert
 */
class log
{
    const INFO = "addInfo";
    const WARNING = "addWarning";
    const ERROR = "addError";
    
    private static $_log = null;
    
    public static function setAppLog($msg, $mode = self::INFO)
    {
        if (!config::getConfigParam(array("application", "enable_logs"))->value)
        {
            return false;
        }
        
        $app_log_file = config::getConfigParam(array("application", "log_path"))->value;
        $handle = fopen($app_log_file, 'a');
        if($handle === false)
        {
            return false;
        }

        self::$_log = new Logger('application');
        self::_createLogHandler($app_log_file, $mode);
        self::$_log->$mode($msg);
        fclose($handle);
        
        return true;
    }
    
    public static function setCustomerLog($msg, $mode = self::INFO)
    {
        if (!config::getConfigParam(array("application", "enable_logs"))->value)
        {
            return false;
        }
        
        $customer_log_file = config::getConfigParam(array("application", "customer_log_path"))->value;
        $handle = fopen($customer_log_file, 'a');
        if($handle === false)
        {
            return false;
        }
        
        self::$_log = new Logger('customer');
        self::_createLogHandler($customer_log_file, $mode);
        self::$_log->$mode($msg);
        fclose($handle);
        
        return true;
    }
    
    private static function _createLogHandler($file_path, $mode)
    {
        switch($mode)
        {
            case "warning":
                self::$_log->pushHandler(new StreamHandler($file_path, Logger::WARNING));
            break;
            case "error":
                self::$_log->pushHandler(new StreamHandler($file_path, Logger::ERROR));
            break;
            default:
                self::$_log->pushHandler(new StreamHandler($file_path, Logger::INFO));
            break;
        }
    }
}