<?php

namespace core\frontend\controller;

// Controllers
use core\helpers\controller\helpers;

/**
 * Redsys controller
 *
 * @author Dani Gilabert
 * 
 */
class redsys
{
    private $_data = null;
    
    public function __construct($data)
    {
        $this->_data = $data;
    }
    
    public function isRedsys()
    {
        
        return (isset($_SERVER['REDIRECT_URL']) && $_SERVER['REDIRECT_URL'] === "/redsys/onlinenotification");
    }
    
    public function getParams()
    {
        $this->_data['controller'] = 'modules\\'.'ecommerce'.'\frontend\controller\payment';
        $this->_data['method'] = 'onRedsysOnlineNotification';   

        $params = helpers::objectize($this->_data);
        return $params;
    }
    
}