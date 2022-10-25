<?php

namespace core\frontend\controller;

// Controllers
use core\lang\controller\lang as coreLang;

/**
 * Handle frontend language behaviour
 *
 * @author Dani Gilabert
 * 
 */
class lang extends coreLang
{          
 
    public static function trans($id, $lang = null)
    {
        return self::_trans($id, $lang);
    } 
 
    protected static function _trans($id, $lang = null)
    {
        $lang_path = static::_getLangPath($lang);
        
        if(!file_exists($lang_path))
        {
            return '?';
        }
        
        require($lang_path);
        
        if (!isset($trans[$id]))
        {
            return '?';
        }
        
        return $trans[$id];
    }         
 
    public static function getKey($value, $lang)
    {
        return self::_getKey($value, $lang);
    }
    
    protected static function _getKey($value, $lang)
    {
        $lang_path = static::_getLangPath($lang);
        
        if(!file_exists($lang_path))
        {
            return false;
        }
        
        require($lang_path);
        
        return array_search($value, $trans);
    }
    
}