<?php

namespace core\config\controller;

// Controllers
use Exception;
use core\url\controller\url;
use core\model\controller\model;
use core\backend\controller\session as backendSession;
use core\helpers\controller\image;
use core\globals\controller\globals;
//use core\debug\controller\debug;

/**
 * Handle main application configuration options
 *
 * @author Dani Gilabert
 */
class config
{
    private static $_config = null;
    private static $_domain = null;
    
    public static function getConfig($db_id)
    {
        if (isset(self::$_config))
        {
            return self::$_config;
        }
        
        $globalvar = globals::getGlobalVar('config');
        if (isset($globalvar))
        {
            self::$_config = $globalvar;
            return $globalvar;
        }
        
        $db = new model();
        $db->setDbId($db_id);
        $doc = $db->get('config');  
        
        if (!isset($doc))
        {
            // Insert main configuration document layout when it doesn't exist
            $json = '{"setting_up_date": "'.date("d-m-Y H:i:s").'"}';
            $doc = json_decode($json); 
            $ret = $db->insert('config', $doc);
            if (isset($ret) && $ret[1])
            {
                $doc->_rev = $ret[1];
            }
            throw new Exception("Error getting config doc. db_id: ".$db_id);
        }
        
        self::setConfig($doc);
        
        return self::$_config;
    }

    public static function updateConfig($doc, $db_id)
    {
        $db = new model();
        $db->setDbId($db_id);
        $ret = $db->update('config', $doc);
        if (isset($ret) && $ret[1])
        {
            $doc->_rev = $ret[1];
        }
        
        self::setConfig($doc);
    }
    
    public static function setConfig($doc)
    {
        self::$_config = $doc;
        globals::setGlobalVar('config', $doc);
    }
         
    public static function getConfigParam($arr, $replace_tags = true, $db_id = 'main_database')
    {
        // First, get the config document
        $doc = self::getConfig($db_id);   
        if (!isset($doc))
        {
            throw new Exception("Error getting config param. db_id: ".$db_id);
        }

        // Check if the parameter is in db
        $is = true;
        $object = $doc;
        foreach($arr as $key)
        {
            $property = $key;
            if (!isset($object->$property))
            {
                $is = false;
                break;
            }            
            $object = $object->$property;
        }

        // If the parameter is in db, return the values,
        // otherwise, check if the parameter is in the config.json file
        if(!$is)
        {
            $config_file = 'config.json';
            if(!file_exists($config_file))
            {
                throw new Exception("Config file: '".$config_file."' doesn't exist.");
            }             
            $object_file = json_decode(file_get_contents($config_file));
            $is = true;
            $param = '';
            foreach($arr as $key)
            {
                $param .= '->'.$key;
                if(!isset($object_file->$key))
                {
                    $is = false;
                    throw new Exception("Parameter: '".$param."' doesn't exist in Config file: '".$config_file."'");
                }            
                $object_file = $object_file->$key;
            }          
            
            // The param
            $object = $object_file;
            $object->value = $object_file->default_value;
            
            // Update doc
            // We have to do a recursive method ..... (later...)
            switch(count($arr))
            {
                case 1:
                    $arr0 = $arr[0];
                    $doc->$arr[0] = $object;
                    break;
                case 2:
                    $arr0 = $arr[0];
                    $arr1 = $arr[1];
                    $doc->$arr0->$arr1 = $object;
                    break;
                case 3:
                    $arr0 = $arr[0];
                    $arr1 = $arr[1];
                    $arr2 = $arr[2];
                    $doc->$arr0->$arr1->$arr2 = $object;
                    break;
                case 4:
                    $arr0 = $arr[0];
                    $arr1 = $arr[1];
                    $arr2 = $arr[2];
                    $arr3 = $arr[3];
                    $doc->$arr0->$arr1->$arr2->$arr3 = $object;
                    break;                    
            }         
            self::updateConfig($doc, $db_id);
        }
        
        if ($replace_tags && !is_object($object->value) && !is_array($object->value))
        {
            $object->value = self::_replaceTags($object->value);
        }
        
        return $object;        
            
    }
    
    public static function getInitParam($arr, $replace_tags = true)
    {
        $init_file = self::getProjectPath()."/init.json";
        if(!file_exists($init_file))
        {
            throw new Exception("Init file: '".$init_file."' doesn't exist."); 
            return false;
        }   
         
        $object_file = json_decode(file_get_contents($init_file));      
        $is = true;
        $param = '';
        foreach($arr as $key)
        {
            $param .= '->'.$key;
            if(!isset($object_file->$key))
            {
                $is = false;
                throw new Exception("Parameter: '".$param."' doesn't exist in Init file: '".$init_file."'");
                break;
            }            
            $object_file = $object_file->$key;
        }      
        
        // The param
        $object = $object_file;
        
        if ($replace_tags && !is_object($object->value) && !is_array($object->value))
        {
            $object->value = self::_replaceTags($object->value);
        }
        
        return $object;
    }
    
    public static function getDBConnectionParams($connection_name = null)
    {
        if(is_null($connection_name))
        {
            throw new Exception("Connection name doesn't exist or is null."); 
        }          
        $ret = self::getInitParam(array("db", $connection_name));
        
        return $ret; 
    }
    
    public static function getAppVersion()
    {
        $version_file = "version";
        if(file_exists($version_file))
        {
            return rtrim(file_get_contents($version_file));
        }
        
        return "N/A";
    }
    
    public static function getAppLogo()
    {
        $image_path = self::getConfigParam(array("application", "logo"))->value;
        $server_scope = $image_path->server_scope;
        $client_scope = $image_path->client_scope;
        
        $ret = image::getImageProperties($server_scope);
        $ret->client_path = $client_scope;
        
        return $ret;
    }
      
    private static function _replaceTags($string)
    {
        $ret = $string;
        
        // Logged user
        $needle = '%U%';
        if (strpos($string, $needle) !== false) 
        {
            $replacement = (backendSession::getLoggedUser()) ? backendSession::getLoggedUser() : null;
            $ret = str_replace($needle, $replacement, $ret);
        }
        
        // Base path
        $needle = '%BP%';
        if (strpos($string, $needle) !== false) 
        {
            $replacement = self::getConfigParam(array("application", "base_path"))->value;
            $ret = str_replace($needle, $replacement, $ret);
        }
        
        return $ret;
    }
    
    public static function setInitBehaviour()
    {
        date_default_timezone_set(self::getConfigParam(array("application", "timezone"))->value);
        define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
        
        if (self::getConfigParam(array("application", "development"))->value)
        {
            error_reporting(E_ALL);
            ini_set('display_errors', TRUE);
            ini_set('display_startup_errors', TRUE);
            /*
            debug::setValue(array(
                'key' => 'current_memory_usage',
                'value' => ((memory_get_usage(true) / 1024 / 1024) . " MB"),
                'description' => 'Current memory usage'
            ));
            */
        }
    }
    
    public static function getAllModules()
    {
        $modules = config::getInitParam(array("modules"))->value; 
        foreach ($modules as $module_id)
        {
            $ret[] = $module_id;
        }
        
        return $ret;
    }
    
    public static function getProjectPath($domain = null)
    {
        if (!is_null($domain))
        {
            $server_name = $domain;
        }
        else
        {
            $server_name = is_null(self::$_domain)? (url::getServerName()) : self::$_domain;
        }
        
        $raw_prj_name = str_replace("www.", "", $server_name);
        $prj_name_pieces = explode(".", $raw_prj_name);
        $prj_name = $prj_name_pieces[0];
        return "prj/$prj_name";
    }
    
    public static function getPublicPath($domain = null)
    {
        return config::getProjectPath($domain).'/public';  
    }
    
    public static function getFilemanagerPath($domain = null)
    {
        return config::getPublicPath($domain).'/filemanager';  
    }
    
    public static function getBotplusPath($domain = null)
    {
        return config::getPublicPath($domain).'/botplus';  
    }
    
    public static function getGooglePath($domain = null)
    {
        return config::getPublicPath($domain).'/google';  
    }
    
    public static function getBingPath($domain = null)
    {
        return config::getPublicPath($domain).'/bing';  
    }
    
    public static function getRobotsPath($domain = null)
    {
        return config::getPublicPath($domain).'/robots.txt';  
    }
    
    public static function getSitemapPath($domain = null)
    {
        return config::getPublicPath($domain).'/sitemap';  
    }
    
    public static function setDomain($domain) {
        self::$_domain = $domain;
    }
}