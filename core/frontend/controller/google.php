<?php

namespace core\frontend\controller;

// Controllers
use core\config\controller\config;

/**
 * Google controller
 *
 * @author Dani Gilabert
 * 
 */
class google
{
    private $_website = null;
    private $_full_path = null;
    
    public function __construct($website)
    {
        $this->_website = $website;
    }
    
    public function isGoogleSiteVerificationRequest()
    {
        $raw_redirect_url = $_SERVER['REDIRECT_URL'];
        $redirect_url = str_replace('/', '', $raw_redirect_url);
        
        if (!isset($redirect_url) ||
            strlen($redirect_url) <= 'google.html' ||
            substr($redirect_url, 0, 6) !== 'google' || 
            substr($redirect_url, strlen($redirect_url) - 5, 5) !== '.html')
        {
            return false;
        }
        
        return $this->_checkFile();
    }
    
    public function dispatchGoogleSiteVerification()
    {
        echo file_get_contents($this->_full_path);
    }
    
    public function isGoogleShoppingFeedRequest()
    {
        if (!isset($_SERVER['REDIRECT_URL']) ||
            $_SERVER['REDIRECT_URL'] != '/googleShoppingFeed.txt')
        {
            return false;
        }
        
        return $this->_checkFile();
    }
    
    public function dispatchGoogleShoppingFeed()
    {
        header("Content-Type: text/plain"); 
        header("Cache-Control: max-age=0");
        echo file_get_contents($this->_full_path);
    }
    
    private function _checkFile()
    {    
        $raw_redirect_url = $_SERVER['REDIRECT_URL'];
        $redirect_url = str_replace('/', '', $raw_redirect_url);
        $pieces = explode('/', $redirect_url);
        $file = $pieces[count($pieces) - 1];
        $this->_full_path = config::getGooglePath().'/'.$file;
        if (!file_exists($this->_full_path))
        {
            return false;
        }
        
        return true;
    }
    
}