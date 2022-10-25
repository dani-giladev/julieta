<?php

namespace core\model\controller;

use \Exception;

/**
 * MySql driver
 *
 * @author Dani Gilabert
 * 
 */
class mysql
{
    private $_connection = null;

    public function connect($conn_params)
    {
        throw new Exception("Not implemented yet.");
    }
    
}