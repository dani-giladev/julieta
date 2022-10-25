<?php

namespace core\model\controller;

// Controllers
use \Exception;
use core\config\controller\config as config;
use core\log\controller\log;

// Views
use core\model\view\couch as view;

/**
 * Couchbase driver
 *
 * @author Dani Gilabert
 * 
 */
class couchbase
{
    private $_connection = null;
    private $_db_id = null;
    
    public function connect($conn_params)
    {
        if(!isset($conn_params->bucket->value))
        {
            throw new Exception("Model cannot be initialized, connection params missed.");
        }
        
        if($conn_params->active->value)
        {
            $this->_connection = new \Couchbase(
                $conn_params->host->value.':'.$conn_params->port->value, 
                $conn_params->bucket->value,
                $conn_params->password->value,
                $conn_params->bucket->value,
                $conn_params->conn_persist->value
            );
        }
        else
        {
            throw new Exception("Bucket <i>".$conn_params->bucket->value."</i> is not active in init.json.");
        }
        
        // Insert main configuration document layout when it doesn't exist
        if($this->getDoc('config') === null)
        {
            $json = '{"setting_up_date": "'.date("d-m-Y H:i:s").'"}';
            $this->insertDoc('config', json_decode($json));
        }
            
        return $this->_connection;
    }
    
    public function getDriver()
    {
        return 'couchbase';
    }
    
    public function getDbId()
    {
        return $this->_db_id;      
    }
    
    public function setDbId($value)
    {
        $this->_db_id = $value;     
    }
    
    public function getDoc($id)
    {
        $json = $this->_connection->get($id);
        $object = json_decode($json);
        
        return $object;
    }
    
    public function insertDoc($id, $document)
    {
        return $this->_connection->set($id, json_encode($document), 0, "", 1);
    }
    
    public function updateDoc($id, $document)
    {
        return $this->_connection->replace($id, json_encode($document), 0, "", 1);
    }
    
    public function deleteDoc($id)
    {
        return $this->_connection->delete($id, "", 1);
    }
    
    public function insertView($name, $map)
    {
        if (config::getConfigParam(array("application", "development"), true, $this->_db_id)->value)
        {
            // with '_dev' => Direct to development
            // without '_dev' => Direct to production
            $name = "dev_".$name;            
        }

        $db = $this->_connection;
        $db->setDesignDoc($name, $map);   
        return true;
    }
    
    public function getView($name, $stale = 'update_after', $params = array())
    {
        $document = $name;
        $view = $name;
                
        if (config::getConfigParam(array("application", "development"), true, $this->_db_id)->value)
        {
            // with '_dev' => Direct from development
            // without '_dev' => Direct from production
            $document = "dev_".$name;            
        }        
        $db = $this->_connection;
        
        // Options
        if ($stale === true)
        {
            $stale = 'true';
        }
        elseif ($stale === false)
        {
            $stale = 'false';
        } 
        $options = array('stale' => $stale);
        /* stale=false
         * The index is updated before the query is executed. 
         * This ensures that any documents updated (and persisted to disk) are included in the view. 
         * The client will wait until the index has been updated before the query has executed, and therefore the response will be delayed until the updated index is available.            
         * 
         * stale=true
         * The index is not updated. If an index exists for the given view, then the information in the current index is used as the basis for the query and the results are returned accordingly.
         * 
         * stale=update_after
         * This is the default setting if no stale parameter is specified. The existing index is used as the basis of the query, but the index is marked for updating once the results have been returned to the client.
         * 
         * ref: http://docs.couchbase.com/couchbase-manual-2.0/#index-updates-and-the-stale-parameter
         * 
         */        
        $options = array_merge($options, $params);
        
        try
        {
            $ret = $db->view($document, $view, $options);    
            
//            // Add all ids in log application file
//            foreach ($ret['rows'] as $key => $values) {
//                $msg = $values['id'];
//                log::setAppLog($msg, log::INFO);
//                continue;
//            }
            
        }
        catch(Exception $e)
        {
            $ret = null;
        }        
        
        return $ret;
    }
    
    public function getDataView($name, $type, $stale = 'update_after', $map = null, $params = array())
    {
        // Get view
        $view = $this->getView($name, $stale, $params);
        if(!isset($view))
        {
            // Create view
            if (!isset($map))
            {
                // Get basic map
                $view = new view();
                $view->setName($name);
                $view->setType($type);
                $map = $view->getBasicMapView();                
            }

            $this->insertView($name, $map);   
            // Get view
            $view = $this->getView($name, $stale, $params);    
        }

        return $view;
    }
    
    public function get($id)
    {
        return $this->getDoc($id);
    }
    
    public function update($id, $object)
    {
        $this->updateDoc($id, $object);
    }
    
    public function insert($id, $object)
    {
        $this->insertDoc($id, $object);
    }
    
    public function exists($id)
    {
        try
        {
            return $this->_connection->get($id);
        }
        catch(\CouchbaseException $e)
        {
            log::setAppLog("model->exists has thrown an error. Please, check it out: ".$e, log::ERROR);
            
            return false;
        }
    }
    
    public function delete($id)
    {
        $this->deleteDoc($id);
    }
    
}