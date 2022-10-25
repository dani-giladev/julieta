<?php

namespace core\frontend\controller;

// Controllers
use core\helpers\controller\helpers;

/**
 * Frontend controller
 *
 * @author Dani Gilabert
 * 
 */
class frontend
{
    private $_data = null;
    private $_website = null;
    
    public function __construct($data, $website)
    {
        $this->_data = $data;
        $this->_website = $website;
    }
    
    public function getFrontendParams()
    {
        $this->_data['website'] = $this->_website;
        $this->_data['controller'] = 'modules\cms\frontend\controller\builder';
        $this->_data['method'] = 'init';            
        $params = helpers::objectize($this->_data);
        return $params;
    }
    
}