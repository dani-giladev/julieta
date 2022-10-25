<?php

namespace core\frontend\controller;

// Controllers
use core\config\controller\config;

/**
 * Sitemap controller
 *
 * @author Dani Gilabert
 * 
 */
class sitemap
{
    private $_website = null;
    private $_sitemap_folder = null;
    private $_sitemap_filename = null;
    
    public function __construct($website)
    {
        $this->_website = $website;
        $this->_sitemap_folder = config::getSitemapPath();
    }
    
    public function isSitemapRequest()
    {
        if (!isset($this->_sitemap_folder))
        {
            return false;
        }
        
        // Get sitemap filename
        $this->_sitemap_filename = $this->_getSitemapFilename();
        if (strlen($this->_sitemap_filename) === 0)
        {
            return false;
        }   
        
        // Check if it's sitemap.xml
        if ($this->_sitemap_filename === 'sitemap.xml')
        {
            return file_exists($this->_sitemap_folder.'/'.$this->_sitemap_filename);
        }
        
        // Check if it's sitemap-xx.xml where xx is the language
        if (strlen($this->_sitemap_filename) !== strlen('sitemap-xx.xml'))
        {
            return false;
        }
        if (substr($this->_sitemap_filename, 0, 7) !== 'sitemap' || 
            substr($this->_sitemap_filename, strlen($this->_sitemap_filename)-3, 3) !== 'xml')
        {
            return false;
        }
        
        return file_exists($this->_sitemap_folder.'/'.$this->_sitemap_filename);
    }
    
    public function dispatchSitemap()
    {
        header ("Content-Type:text/xml");
        echo file_get_contents($this->_sitemap_folder.'/'.$this->_sitemap_filename);
    }
    
    private function _getSitemapFilename()
    {
        // Check PATH_INFO
        $filename = (isset($_SERVER['REDIRECT_URL']))? $_SERVER['REDIRECT_URL'] : '';
        if (strlen($filename) > 0)
        {
            $filename = substr($filename, 1); // Remove the first slash
        }   
        return $filename;
    }
    
}