<?php

namespace core\frontend\controller;

// Controllers
use core\config\controller\config;

/**
 * Robots controller
 *
 * @author Dani Gilabert
 * 
 */
class robots
{
    private $_website = null;
    
    public function __construct($website)
    {
        $this->_website = $website;
    }
    
    public function isRobotsRequest()
    {
        if (!isset($_SERVER['REDIRECT_URL']) ||
            $_SERVER['REDIRECT_URL'] != '/robots.txt')
        {
            return false;
        }
        
        if (!file_exists(config::getRobotsPath()))
        {
            return false;
        }
        
        return true;
    }
    
    public function dispatchRobots()
    {
        header("Content-Type: text/plain"); 
        header("Cache-Control: max-age=0");         
        echo file_get_contents(config::getRobotsPath());
    }
    
}