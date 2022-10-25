<?php

namespace core\model\controller;

use \Exception;

/**
 * PostgreSql driver
 *
 * @author Dani Gilabert
 * 
 */
class postgreSql
{
    private $_connection = null;

    public function connect($conn_params)
    {
        throw new Exception("Not implemented yet.");
    }
    
}