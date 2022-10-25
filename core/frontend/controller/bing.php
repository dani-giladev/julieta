<?php

namespace core\frontend\controller;

// Controllers
use core\config\controller\config;

/**
 * Bing controller
 *
 * @author Dani Gilabert
 * 
 */
class bing
{
    private $_website = null;
    private $_full_path = null;
    
    public function __construct($website)
    {
        $this->_website = $website;
    }
    
    public function isBingSiteVerificationRequest()
    {
        $raw_redirect_url = $_SERVER['REDIRECT_URL'];
        $redirect_url = str_replace('/', '', $raw_redirect_url);
        
        if (!isset($redirect_url) ||
            strlen($redirect_url) <= 'BingSiteAuth.xml' ||
            substr($redirect_url, 0, 12) !== 'BingSiteAuth' || 
            substr($redirect_url, strlen($redirect_url) - 4, 4) !== '.xml')
        {
            return false;
        }
        
        return $this->_checkFile();
    }
    
    public function dispatchBingSiteVerification()
    {
        echo file_get_contents($this->_full_path);
    }
    
    private function _checkFile()
    {    
        $raw_redirect_url = $_SERVER['REDIRECT_URL'];
        $redirect_url = str_replace('/', '', $raw_redirect_url);
        $pieces = explode('/', $redirect_url);
        $file = $pieces[count($pieces) - 1];
        $this->_full_path = config::getBingPath().'/'.$file;
        if (!file_exists($this->_full_path))
        {
            return false;
        }
        
        return true;
    }
    
}