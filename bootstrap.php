<?php

// Controllers
use core\debug\controller\debug;
use core\session\controller\session;
use core\config\controller\config;
use core\helpers\controller\helpers;
use core\backend\controller\backend;
use modules\cms\controller\website;
use core\frontend\controller\frontend;
use core\frontend\controller\robots;
use core\frontend\controller\sitemap;
use core\frontend\controller\google;
use core\frontend\controller\bing;
use core\frontend\controller\redsys;

/**
 * Bootstrap class
 *
 * @author Dani Gilabert
 * 
 */
class bootstrap
{
    private $_data = null;
    private $_domain = null;
    private $_website = null;
    
    /**
    * This method is always called from the index.php. It redirect all the ajax request to theirs controllers methods
    * and render the backend viewport, too.
    * @return Html code to render the backend viewport or redirect to other controller method from ajax request
    */     
    public function init()
    {
        debug::setStartTime(microtime(true));
        
        //$this->_data = (count($_POST) > 0) ? $_POST : $_GET;
        $this->_data = $_REQUEST;
        unset($this->_data['XDEBUG_SESSION_START']); // Clean bad parameters when debugging
        $this->_domain = $_SERVER['SERVER_NAME'];
                
        // Is Redsys?    
        $redsys = new redsys($this->_data);
        if ($redsys->isRedsys())
        {
            $params = $redsys->getParams();
            session::startSession($params->sid);
            $this->_dispatch($params);
            return;
        }

        session::startSession();

        config::setInitBehaviour();
                
        // Is ajax Query?
        if ($this->_isAjaxQuery())
        {
            $this->_dispatch($this->_getAjaxQueryParams()); 
            return;
        }     
        
        // Is backend page?
        $backend = new backend();
        if ($backend->isBackendPage())
        {
            $backend->dispatchBackendPage();
            return;
        }
        
        // Check if there is any website with this domain
        $website_controller = new website();
        $this->_website = $website_controller->getWebsiteByDomain($this->_domain, true, true, true);
        if (empty($this->_website))
        {
            die('Invalid url!');
        }
        
        // Is robots.txt request?
        $robots = new robots($this->_website);
        if ($robots->isRobotsRequest())
        {
            $robots->dispatchRobots();
            return;
        }
            
        // Is sitemap.xml request?
        $sitemap = new sitemap($this->_website);
        if ($sitemap->isSitemapRequest())
        {
            $sitemap->dispatchSitemap();
            return;
        }
            
        // Is Google request?
        $google = new google($this->_website);
        if ($google->isGoogleSiteVerificationRequest())
        {
            $google->dispatchGoogleSiteVerification();
            return;
        }
        if ($google->isGoogleShoppingFeedRequest())
        {
            $google->dispatchGoogleShoppingFeed();
            return;
        }
            
        // Is Bing request?
        $bing = new bing($this->_website);
        if ($bing->isBingSiteVerificationRequest())
        {
            $bing->dispatchBingSiteVerification();
            return;
        }
        
        // Check if a lost image or file
        if ($this->_isLostFile())
        {
            $this->_dispatch404();
            return;
        }
                
        // It's frontend page!
        $frontend = new frontend($this->_data, $this->_website);
        $this->_dispatch($frontend->getFrontendParams());
    }
    
    private function _isAjaxQuery()
    {
        $post_values = filter_input(INPUT_POST, "method", FILTER_SANITIZE_STRING);
        $get_values = filter_input(INPUT_GET, "method", FILTER_SANITIZE_STRING);
        
        return is_null($post_values) ? $get_values : $post_values;
    }
    
    private function _getAjaxQueryParams()
    {
        $params = helpers::objectize($this->_data);
        return $params;
    }
    
    /**
    * Redirect the ajax request to their controller class method
    * @return Throw an exception if the method doesn't exists
    */     
    private function _dispatch($params)
    {
        $controller = $params->controller;
        $method = $params->method;
                
        // Fix controller
        $controller = str_replace('/', '\\', $controller);
            
        if (isset($params->appversion))
        {    
            $vcontroller = $controller.'_'.str_replace('.', '_', $params->appversion);
            if (method_exists($vcontroller, $method))
            {    
                $controllerInstance = new $vcontroller;
                $controllerInstance->$method($params);
                return;
            }            
        }
        
        if (method_exists($controller, $method))
        {    
            $controllerInstance = new $controller;
            $controllerInstance->$method($params);
        }
        else
        {
            throw new \Exception("Error. Method <i>".$method."</i> doesn't exist in ".$controller);
        }
    }
    
    private function _isLostFile()
    {
        $filename = (isset($_SERVER['REDIRECT_URL']))? $_SERVER['REDIRECT_URL'] : '';
        if (strlen($filename) === 0)
        {
            return false;
        }

        if (strlen($filename) < 4)
        {
            return false;
        }
        
        // Check if a image
        $ext = substr($filename, strlen($filename)-4);
        if (strtolower($ext) !== '.jpg' && strtolower($ext) !== '.png' && strtolower($ext) !== '.ico' && strtolower($ext) !== '.gif')
        {
            return false;
        }
        
        return true;
    }
    
    private function _dispatch404()
    {
        //header("HTTP/1.0 404 Not Found");
        $server_protocol = $_SERVER["SERVER_PROTOCOL"];
        header($server_protocol." 404 Not Found");
        header("Status: 404 Not Found");
        $_SERVER['REDIRECT_STATUS'] = "404";
    }
    
}