<?php

namespace core\lang\controller;

use core\config\controller\config;

// Libs
//use Stichoza\GoogleTranslate\GoogleTranslate;
use \Statickidz\GoogleTranslate;

/**
 * Handle global language behaviour
 *
 * @author Dani Gilabert
 * 
 */
class lang
{
    
    /**
    * Get the application supported languages defined in init config file
    * @return object . Return the code and description of the application supported languages
    */ 
    public static function getSupportedLanguages()
    {
        $supported_languages = config::getInitParam(array("languages"));
        $ret = $supported_languages->value;
        return $ret;
    }
    
    /**
    * Only get code of the application supported languages defined in init config file
    * @return object . Only return the code of the application supported languages
    */     
    public static function getSupportedCodeLanguages()
    {
        $langs = self::getSupportedLanguages();
        $ret= new \stdClass();
        foreach ($langs as $key => $value)
        {
            $ret->$key =$value->code;
        }         
        return $ret;
    }
    
    /**
    * Get the main language code defined in browser config
    * @return string . Return the main language code browser
    */ 
    public static function getBrowserDefaultLanguage()
    {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
        {
            return null;
        }
        
        $browser_langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $main_lang = explode('-', $browser_langs[0]);
        $ret = $main_lang[0];

        return $ret;
    }
    
    public static function setLocales($lang)
    {
        putenv("LANG=".$lang);
        setlocale(LC_ALL, $lang);
    }
    
    public static function translate($source_lang_code, $target_lang_code, $source_text)
    {
//        $translator = new GoogleTranslate($source_lang_code, $target_lang_code);
//        return $translator->translate($source_text);
        
        $translator = new GoogleTranslate();
        return $translator->translate($source_lang_code, $target_lang_code, $source_text);

    }
}