<?php

namespace core\backend\controller;

// Controllers
use core\lang\controller\lang as coreLang;
use core\config\controller\config;
use core\backend\controller\session;

/**
 * Handle backend language behaviour
 *
 * @author Dani Gilabert
 * 
 */
class lang extends coreLang
{       
    
    /**
    * Global translation action for server side labels
    * @param string . $label.
    * @return string . Return the main language code browser
    */ 
    public static function trans($label, $caller = null)
    {
        $current_lang = self::getLanguage();
        $module_split = preg_split("/\\\/", $caller);
        $module = $module_split[1];
        $lang_path = 'modules/'.$module.'/backend/res/lang/server/'.$current_lang.'.json';
        if(is_null($caller) || !file_exists($lang_path))
        {
            return false;        
        }
        
        $trans = json_decode(file_get_contents($lang_path), true);
        
        return $trans[$label];
    }   
    
    public static function getLanguage()
    {
        // first, get the supported code languages
        $available_langs = (array) self::getSupportedCodeLanguages();   

        // Get the session lang
        $session_lang = session::getSessionVar('loginLang');
        if(in_array($session_lang, $available_langs))
        {
            return $session_lang;
        }

        // Get the browser lang
        $browser_lang = self::getBrowserDefaultLanguage();
        if(isset($browser_lang))
        {
            if(in_array($browser_lang, $available_langs))
            {
                return $browser_lang;
            }            
        }
            
        // Get the default app/config language
        $default_language =  config::getConfigParam(array("application", "default_language"))->value;
        if(in_array($default_language, $available_langs))
        {
            return $default_language;
        }               
            
        // By default
        return 'en';
    }   
    
}