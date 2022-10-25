<?php

namespace core\model\controller;

// Controllers
use core\config\controller\config as config;

/**
 * Microsoft SQL Server driver
 *
 * @author Dani Gilabert
 * 
 */
class mssql
{
    private $_connection = null;
    
    public function connect($conn_params)
    {
        $enabled = $conn_params->enabled;
        if (!$enabled)
        {
            return null;
        }
        
        $adobb_path = config::getConfigParam(array("application", "adobb_path"))->value;
        
        require_once($adobb_path.'/adodb-exceptions.inc.php');
        //require_once($adobb_path.'/adodb-errorhandler.inc.php');
        require_once($adobb_path.'/adodb.inc.php');

        $type = 'mssql';
        $fetch_type = 'ADODB_FETCH_ASSOC';
        
        $host = $conn_params->host;
        $port = $conn_params->port; // Default port = 1433
        $db = $conn_params->db;
        $user = $conn_params->user;
        $pass = $conn_params->pass;
        
        // Disable warnings
        $last_error_reporting = error_reporting();
        error_reporting(E_ALL && ~E_STRICT && ~E_NOTICE);
        
        try 
        {
            if (version_compare(phpversion(), '7.0', '>='))
            {
                $this->_connection = NewADOConnection("pdo");
                $this->_connection->Connect
                (
                    "dblib:host=$host:$port;dbname=$db",
                    $user,
                    $pass
                );
            }
            else
            {
                $this->_connection = NewADOConnection($type);
                $this->_connection->Connect
                (
                    $host.':'.$port,
                    $user,
                    $pass,
                    $db
                );
            }
        } 
        catch (\Exception $e) 
        {
            error_reporting($last_error_reporting);
            $this->_connection = null;
            $msg = $e->getMessage();
            return false;
        }        

        error_reporting($last_error_reporting);
        $this->_connection->SetFetchMode($fetch_type);
        return true;
    }
    
    public function isConnected()
    {
        return (!is_null($this->_connection));
    }
    
    public function close()
    {
        $this->_connection->close();
    }
    
    public function execute($sql)
    {
        return $this->_connection->Execute($sql);
    }
    
}