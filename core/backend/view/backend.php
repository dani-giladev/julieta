<?php

namespace core\backend\view;

// Controllers
use core\config\controller\config;
use core\backend\controller\lang;
use core\backend\controller\session;

/**
 * Backend view
 *
 * @author Dani Gilabert
 * 
 */
class backend
{
    public $app_code;
    public $app_title; 
    public $app_version; 
    public $app_extjslibs; 
    public $app_base_path;
    public $app_logo; 
    public $app_dateformat;
    public $app_dateformat_database;
    public $app_decimal_separator;
    public $app_erp_interface_description;
    public $logged_user;
    public $logged_lang;
    public $logged_full_name_user;
    public $is_super_user;
    public $filemanager_path;    
    public $ecommerce_erp_interface_code;
    public $ecommerce_vat_is_always_inclued_to_cost_price;
    public $ecommerce_only_one_delegation;
    
    public function __construct()
    {
        $this->app_code = $this->_getAppCode(); 
        $this->app_title = $this->_getAppTitle(); 
        $this->app_version = $this->_getAppVersion(); 
        $this->app_extjslibs = $this->_getAppExtjsLibs(); 
        $this->app_base_path = $this->_getAppBasePath();
        $this->app_logo = $this->_getAppLogo(); 
        $this->app_dateformat = $this->_getAppDateFormat();
        $this->app_dateformat_database = $this->_getAppDateFormatDatabase();
        $this->app_decimal_separator = $this->_getAppDecimalSeparator();
        $this->logged_user = $this->_getLoggedUser();
        $this->logged_lang = $this->_getLoggedLang();
        $this->logged_full_name_user = $this->_getLoggedFullNameUser();
        $this->is_super_user = $this->_isSuperUser();
        $this->filemanager_path = config::getFilemanagerPath();
        
        if ($this->app_code === 'maryann')
        {
            $this->ecommerce_erp_interface_code = $this->_getEcommerceERPInterfaceCode();
            $this->app_erp_interface_description = $this->_getAppERPInterfaceDescription($this->ecommerce_erp_interface_code);
            $this->ecommerce_vat_is_always_inclued_to_cost_price = $this->_getEcommerceVatIsIncludedToCostPrice();
            $this->ecommerce_only_one_delegation = $this->_getEcommerceOnlyOneDelegation();
        }        
    }
    
    private function _getAppCode()
    {
        return config::getConfigParam(array("application", "code"))->value;
    }
    
    private function _getAppTitle()
    {
        return config::getConfigParam(array("application", "title"))->value;
    }
    
    private function _getAppVersion()
    {
        return config::getAppVersion();
    }
    
    private function _getAppExtjsLibs()
    {
        return (config::getConfigParam(array("application", "development"))->value) ? "ext-debug.js" : "ext-all.js";
    }
    
    private function _getAppLogo()
    {
        return config::getAppLogo();
    }
    
    private function _getAppBasePath()
    {
        return config::getConfigParam(array("application", "base_path"))->value;
    }
    
    private function _getAppDateFormat()
    {
        return config::getConfigParam(array("application", "dateformat"))->value;
    }
    
    private function _getAppDateFormatDatabase()
    {
        return config::getConfigParam(array("application", "dateformat_database"))->value;
    }
    
    private function _getAppDecimalSeparator()
    {
        return config::getConfigParam(array("application", "decimal_separator"))->value;
    }
    
    private function _getLoggedUser()
    {
        $user = session::getLoggedUser();
        if ($user)
        {
            return $user->code;
        }
        else
        {
            return null;
        }
    }
    
    private function _getLoggedFullNameUser()
    {
        $user = session::getLoggedUser();
        if ($user)
        {
            $full_name = $user->firstName.' '.
                         $user->lastName;
            return $full_name;  
        }
        else
        {
            return null;
        }
    }
    
    private function _isSuperUser()
    {
        $user = session::getLoggedUser();
        if ($user)
        {
            return ($user->superUser)? 1 : 0;  
        }
        else
        {
            return 0;
        }
    }
    
    private function _getLoggedLang()
    {
        return lang::getLanguage();
    }
    
    private function _getEcommerceERPInterfaceCode()
    {
        $ret = '';
        $erpecommerce_interface = config::getConfigParam(array("ecommerce", "erp_interface"))->value;
        if (!$erpecommerce_interface->enabled || empty($erpecommerce_interface->type))
        {
            return $ret;
        }
        
        $interface_code = $erpecommerce_interface->type;
        $interface = config::getConfigParam(array("application", $interface_code))->value;
        if (!isset($interface) || !$interface->enabled)
        {
            return $ret;
        }
        
        return $interface_code;
    } 
    
    private function _getAppERPInterfaceDescription($interface_code)
    {
        $ret = '';
        if (empty($interface_code))
        {
            return $ret;
        }
        
        return config::getConfigParam(array("application", $interface_code))->description;
    }
    
    private function _getEcommerceVatIsIncludedToCostPrice()
    {
        return config::getConfigParam(array("ecommerce", "vat_is_always_inclued_to_cost_price"))->value;
    }
    
    private function _getEcommerceOnlyOneDelegation()
    {
        return config::getConfigParam(array("ecommerce", "only_one_delegation"))->value;
    }
    
}