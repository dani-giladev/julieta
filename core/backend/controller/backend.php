<?php

namespace core\backend\controller;

// Controllers
use core\config\controller\config;
use core\ajax\controller\ajax;
use core\backend\controller\trans;
use core\backend\controller\lang;
use core\helpers\controller\helpers;

// Views
use core\backend\view\backend as view;

/**
 * Backend controller
 *
 * @author Dani Gilabert
 * 
 */
class backend
{
    public $view = null;
    public $module_id = null;
    public $trans_instances = array();
    public $server_translations = array();

    public function __construct()
    {
        $this->module_id = 'core';
    }
    
    public function setView()
    {
        $this->view = new view();
    }
    
    public function isBackendPage()
    {
        $uri = str_replace("?XDEBUG_SESSION_START=netbeans-xdebug", "", $_SERVER['REQUEST_URI']); // Avoid bad url when debugging
        return ($uri === '/login') ? true : false;
    }
    
    public function dispatchBackendPage()
    {
        $is_development = config::getConfigParam(array("application", "development"))->value;
        
        $url = '/UI';
        
        if ($is_development)
        {
            $url .= '-dev';
        }
        
        header("Location:".$url);
    }
    
    public function getGlobalConfig()
    {
        $lang = lang::getLanguage();
        $item['id'] = 'lang';
        $item['val'] = $lang;
        $ret[] = $item;        

        $ret = helpers::objectize($ret);  
        ajax::sendData($ret);            
    }
    
    public function getCountriesList()
    {
        $lang = lang::getLanguage();
        $list = helpers::getCountriesList($lang);
        foreach ($list as $key => $value)
        {
            $item['code'] = $key;
            $item['name'] = $value;
            $ret[] = $item;             
        }        

        $ret = helpers::objectize($ret);  
        ajax::sendData($ret);
    }
    
    public function getLanguagesList()
    {
        $lang = 'en'; // There is only one available language (english)
        $list = helpers::getLanguagesList($lang);
        foreach ($list as $key => $value)
        {
            $item['code'] = $key;
            $item['name'] = $value;
            $ret[] = $item;             
        }        

        $ret = helpers::objectize($ret);  
        ajax::sendData($ret);
    }
    
    private function _getTransInstance($module_id)
    {
        if (!isset($this->trans_instances[$module_id]))
        {
            $this->trans_instances[$module_id] = new trans($module_id);
        }
        
        return $this->trans_instances[$module_id];        
    }
    
    private function _getServerTranslations($module_id)
    {
        $trans = $this->_getTransInstance($module_id);
        
        if (!isset($this->server_translations[$module_id]))
        {
            $this->server_translations[$module_id] = $trans->getTranslations($trans::SERVER); 
        }
        
        return $this->server_translations[$module_id];        
    }

    public function getClientTranslations($data)
    {
        $module_id = $data->module_id;
            
        $trans = $this->_getTransInstance($module_id);
        /*
         * Disable on 15th December of 2016 (Dani)
         * 
        // Check if we have to create the new json translations files
        $trans->createTranslationsFiles();
        */
        
        $translations = $trans->getTranslations($trans::CLIENT);
        ajax::sendData($translations);
    }
    
    public function trans($id, $module_id = null)
    {
        if ($module_id == null)
        {
            $module_id = $this->module_id;
        }
        
        $translations = $this->_getServerTranslations($module_id);
        
        if (isset($translations->$id))
        {
            return $translations->$id;
        }
        else
        {
            return '';
        }
            
    }

    public function removeHtmlTags($data)
    {
        $html = $data->html;
        $new_html = helpers::stripHtmlTags($html);
        echo $new_html;
    }

    public function translate($data)
    {
        $source_lang_code = $data->source_lang_code;
        $target_lang_code = $data->target_lang_code;
        $source_text = $data->source_text;
        $source_text = str_replace(array('< /', ' />'), array('</', '/>'), $source_text);
        $source_text = preg_replace('/\/\s/', '/', $source_text);
        $source_text = html_entity_decode($source_text);
        
        // Set tags to replace
        $tags = array('<b>', '</b>', '<i>', '</i>', '<u>', '</u>', '<br>', '</br>', '<ul>', '</ul>', '<ol>', '</ol>', '<li>', '</li>', '<p>', '</p>', '<a>', '</a>', '<em>', '</em>', '<strong>', '</strong>', '<code>', '</code>', '<samp>', '</samp>', '<kbd>', '</kbd>', '<var>', '</var>', '<br/>');
        $keys = array();
        foreach ($tags as $index => $value) {
            array_push($keys, '['.$index.']');
            //array_push($keys, '|'.$index.'|');
        }
        
        // Replace tags by keys
        $source_text = str_replace($tags, $keys, $source_text);
        
        // Strip tags
//        $source_text = helpers::stripHtmlTags($source_text, '<b><i><u><br><ul><ol><li><p><a><em><code><samp><kbd><var>');
        $source_text = helpers::stripHtmlTags($source_text, '<br>');
        
        // Translate!!
        $translation = lang::translate($source_lang_code, $target_lang_code, $source_text);
        
        // Replace keys by tags
        //$translation = str_replace(array(' [', '] ', '[ ', ' ]'), array('[', ']', '[', ']'), $translation);
        $translation = str_replace(array('] ', '[ ', ' ]'), array(']', '[', ']'), $translation);
        $translation = str_replace($keys, $tags, $translation);
        
        echo $translation;
    }
    
}