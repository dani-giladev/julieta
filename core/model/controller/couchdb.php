<?php

namespace core\model\controller;

// Controllers
use \Exception;
use core\helpers\controller\helpers;

// Views
use core\model\view\couch as view;
use core\model\view\couchdb as couchdbview;

/**
 * Couchdb driver
 *
 * @author Dani Gilabert
 * 
 */
class couchdb
{
    private $_connection = null;
    private $_db_id = null;
    
    public function connect($conn_params)
    {
        if (!$conn_params->active->value)
        {
            throw new Exception("Database <i>".$conn_params->dbname->value."</i> is not active in init.json.");
        }
        
        $this->_connection = \Doctrine\CouchDB\CouchDBClient::create(array(
            "host"          => $conn_params->host->value,
            "port"          => $conn_params->port->value,
            "dbname"        => $conn_params->dbname->value,
            "user"          => $conn_params->user->value,
            "password"      => $conn_params->password->value,
            "timeout"       => 10.0
        ));        
        
        return $this->_connection;
    }
    
    public function getDriver()
    {
        return 'couchdb';
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
        try 
        {
            $get_ret = $this->_connection->findDocument($id);
        } catch (\Doctrine\CouchDB\HTTP\HTTPException $ex) {
            return null;
        }
        
        if ($get_ret->status !== 200)
        {
            return null;
        }
        
        $json = json_encode($get_ret->body);
        $ret = json_decode($json);
        return $ret;
    }
    
    public function insertDoc($id, $document)
    {
        $doc = (array) $document;
        $doc['_id'] = $id;
        unset($doc['_rev']);
        
        try 
        {
            $ret = $this->_connection->postDocument($doc);
        } catch (\Doctrine\CouchDB\HTTP\HTTPException $ex) {
            return null;
        }
        
        return $ret;
    }
    
    public function updateDoc($id, $document)
    {
        $doc = (array) $document;
        if (!isset($doc['_rev']))
        {
            $document = $this->getDoc($id);
            $doc['_rev'] = $document->_rev;
        }
        
        try 
        {
            $ret = $this->_connection->putDocument($doc, $id, $doc['_rev']);
        } catch (\Doctrine\CouchDB\HTTP\HTTPException $ex) {
            return null;
        }
        
        return $ret;
    }
    
    public function deleteDoc($id)
    {
        $doc = $this->getDoc($id);
        if ($doc === null)
        {
            return null;
        }
        
        $rev = $doc->_rev;
        
        try 
        {
            $this->_connection->deleteDocument($id, $rev);
        } catch (\Doctrine\CouchDB\HTTP\HTTPException $ex) {
            return null;
        }
        
        return true;
    }
    
    public function getDataView($name, $type, $stale = 'update_after', $map = null, $params = array())
    {
        // Get view
        $view = $this->getView($name, $stale, $params);
        if (!isset($view))
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
    
    public function getView($name, $stale = 'update_after', $params = array())
    {
        try
        {
            $query = $this->_connection->createViewQuery($name, $name);
            $query->setReduce(false);
//            $query->setIncludeDocs(true);
            
            if ($stale !== false && strtolower($stale) !== 'false')
            {
                if ($stale === true || strtolower($stale) === 'true')
                {
                    $stale = true;
                }
                $query->setStale($stale);
            }
            
            // Set options
            if (!empty($params))
            {
                foreach ($params as $key => $value) {
                    $option = 'set'.ucfirst($key);
                    $query->$option($value);
                }
            }
            
            $result = $query->execute();
        }
        catch (\Doctrine\CouchDB\HTTP\HTTPException $ex)
        {
            return null;
        }        
        
        $arr = array();
        $arr['rows'] = $result->toArray();
        $ret = helpers::objectize($arr);
        return $ret;
    }
    
    public function insertView($name, $map)
    {
        $couchdbview = new couchdbview($map);
        try 
        {
            $this->_connection->createDesignDocument($name, $couchdbview);
        } catch (\Doctrine\CouchDB\HTTP\HTTPException $ex) {}
    }
    
    public function get($id)
    {
        return $this->getDoc($id);
    }
    
    public function update($id, $object)
    {
        return $this->updateDoc($id, $object);
    }
    
    public function insert($id, $object)
    {
        return $this->insertDoc($id, $object);
    }
    
    public function exists($id)
    {
        try 
        {
            $find_ret = $this->_connection->findDocument($id);
        } catch (\Doctrine\CouchDB\HTTP\HTTPException $ex) {
            return null;
        }
        
        $ret = ($find_ret->status === 200);
        return $ret;        
    }
    
    public function delete($id)
    {
        $this->deleteDoc($id);
    }

    public function replicate($rsource_conn_params, $rtarget_conn_params, $continous = false, $invert_source = false)
    {
        // Source
        $loginSource = $rsource_conn_params->user->value.":".$rsource_conn_params->password->value."@";
        $source = "http://".$loginSource.$rsource_conn_params->host->value.":".$rsource_conn_params->port->value."/".$rsource_conn_params->dbname->value;
        // Target
        $loginTarget = $rtarget_conn_params->user->value.":".$rtarget_conn_params->password->value."@";
        $target = "http://".$loginTarget.$rtarget_conn_params->host->value.":".$rtarget_conn_params->port->value."/".$rtarget_conn_params->dbname->value;

        if($invert_source)
        {
            $url = "http://".$rsource_conn_params->host->value.":".$rsource_conn_params->port->value."/_replicate";
        }
        else
        {
            $url = "http://".$rtarget_conn_params->host->value.":".$rtarget_conn_params->port->value."/_replicate";
        }

        $post_fields = json_encode(array(
            "source"        => $source,
            "target"        => $target,
            "create_target" => true,
            "continuous"    => $continous                
        ));        
        
        // abrimos la sesión cURL
        $ch = curl_init();

        // definimos la URL a la que hacemos la petición
        curl_setopt($ch, CURLOPT_URL, $url);
        // indicamos el tipo de petición: POST
        curl_setopt($ch, CURLOPT_POST, TRUE);
        // definimos cada uno de los parámetros
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1000000);
        
        // recibimos la respuesta y la guardamos en una variable
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $remote_server_output = curl_exec ($ch);

        // cerramos la sesión cURL
        curl_close ($ch);

        if (strpos($remote_server_output, '"ok":true') !== false)
        {
            return true;
        }
        else
        {
            return $remote_server_output;
        }
    }
    
}