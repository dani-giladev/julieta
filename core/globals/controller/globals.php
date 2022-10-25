<?php

namespace core\globals\controller;

// Controllers
use core\session\controller\session;
use core\apc\controller\apc;

/**
 * Golbal Controller
 *
 * @author Dani Gilabert
 * 
 */
class globals
{    
    private static $_var = array();
    private static $_enable_globals = true;
    
    
    public static function enableGlobals($value)
    {
        self::$_enable_globals = $value;
    }
    
    /**
    * Get a global value
    * 
    * @param $key : string . The var key
    * @param $use_apc : optional . boolean
    *       true  : use apc user cache
    *       false : use session vars
    * 
    * @return the global var value 
    */      
    public static function getGlobalVar($key, $use_apc = true)
    {
        if (!self::$_enable_globals)
        {
            return null;
        }
        
        if (isset(self::$_var[$key]))
        {
            $values = self::$_var[$key];
        }
        else
        {
            if (!$use_apc)
            {
                $values = session::getSessionVar($key);
            }
            else
            {
                $values = apc::get($key);
            }            
        }
        
        if (!isset($values) || !is_array($values) || !isset($values['date']) || !isset($values['value']))
        {
            return null;
        }
        
        if (self::hasExpired($values))
        {
            return null;
        }
        
        return $values['value'];
    }
    
    /**
    * Set or save a global value
    * 
    * @param $key : string . The var key
    * @param $value : any type . The value (string, array, object, etc)
    * @param $use_apc : optional . boolean
    *       true  : use apc user cache
    *       false : use session vars
    * @param $expiration : integer . Expiration in minutes
    *       > 0 : the var is going to expire in x minutes (eg: 1440)
    *       = 0 : the var is always expired
    *       < 0 : the var will nerver expire
    * 
    * @return true if the var has been saved successfully
    */     
    public static function setGlobalVar($key, $value, $use_apc = true, $expiration = -1)
    {
        if (!self::$_enable_globals)
        {
            return;
        }
        
        $values = array(
            'date' => date('Y-m-d H:i:s'),
            'value' => $value,
            'expiration' => $expiration // minutes
        );
        
        if (!$use_apc)
        {
            session::setSessionVar($key, $values);
        }
        else
        {
            $ret = apc::set($key, $values);
        }     

        self::$_var[$key] = $values;
    }
    
    public static function hasExpired($var)
    {
        if (!isset($var['expiration']) || $var['expiration'] === 0)
        {
            return true;
        }
        
        if ($var['expiration'] === -1)
        {
            return false;
        }
        
        $expiration_minutes = $var['expiration'];
        
        $now = strtotime(date('Y-m-d H:i:s'));
        $lastdate = $var['date'];
        $date = strtotime($lastdate.' +'.$expiration_minutes.' minute');
        
        return ($now > $date);
    }
 
}