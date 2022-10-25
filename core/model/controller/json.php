<?php

namespace core\model\controller;

// Controllers
use \Exception;
use core\config\controller\config as config;

/**
 * Json driver (files database)
 *
 * @author Dani Gilabert
 * 
 */
class json
{
    private $_base_path = 'db';
    private $_db_id = null;
    
    public function connect($conn_params)
    {
        if (!$conn_params->active->value)
        {
            throw new Exception("<i>".$conn_params->description."</i> is marked as not active in config.json.");
        }
        
        // Insert main configuration document layout when it doesn't exist
        if($this->getDoc('config') === null)
        {
            $json = '{"setting_up_date": "'.date("d-m-Y H:i:s").'"}';
            $this->insertDoc('config', json_decode($json));
        }
    }
    
    public function getDriver()
    {
        return 'json';
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
        $filename = $this->_base_path.'/'.$id;
        if(!file_exists($filename))
        {
            return null;
        }        
        $object = json_decode(file_get_contents($filename));
        return $object;
    }
    
    public function insertDoc($id, $document)
    {
        $filename = $this->_base_path.'/'.$id;
        $this->updateDoc($id, $document);
        
        if (config::getConfigParam(array("application", "development"))->value)
        {
            chmod($filename, 0777);
        }
    }
    
    public function updateDoc($id, $document)
    {
        $filename = $this->_base_path.'/'.$id;
        $ret = file_put_contents($filename, json_encode($document, JSON_PRETTY_PRINT));
        return $ret;
    }
    
    public function deleteDoc($id)
    {
        $filename = $this->_base_path.'/'.$id;
        unlink($filename);
    }
    
    public function getDataView($name, $type, $stale = 'update_after', $map = null, $params = array())
    {
        $ret = new \stdClass();
        $ret->rows = array();
        
        $pattern = $this->_base_path.'/'.$type.'-*';
        $files = glob($pattern);
        if (empty($files)) return $ret;
        
        foreach ($files as $filename) {
            $object = json_decode(file_get_contents($filename));
            $row = new \stdClass();
            $row->value = $object;
            $ret->rows[] = $row;
        }
        
        return $ret;
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
        $filename = $this->_base_path.'/'.$id;
        return file_exists($filename);
    }
    
    public function delete($id)
    {
        $this->deleteDoc($id);
    }
    
}