<?php

namespace core\apc\controller;

/**
 * ACP User Cache Controller
 *
 * @author Dani Gilabert
 * 
 */
class apc
{
    
    public static function get($key)
    {
        if (!self::exists($key))
        {
            return null;
        }
        
        $ret = \apcu_fetch($key);
        return $ret;
    }
    
    public static function set($key, $value)
    {
        $ret = \apcu_store($key, $value);
        return $ret;
    }
    
    public static function exists($key)
    {
        return \apcu_exists($key);        
    }    

 
}