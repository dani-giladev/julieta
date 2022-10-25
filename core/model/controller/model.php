<?php

namespace core\model\controller;

// Controllers
use \Exception;
use core\config\controller\config;
use core\helpers\controller\helpers;
use core\model\controller\db;
use core\backend\controller\session;

/**
 * Model
 *
 * @author Dani Gilabert
 * 
 */
class model
{
    // Main
    private $_db = null;
    protected $_db_id = 'main_database';
    protected $_properties = array();
    protected $_publication_mode = 'SAME_DOCUMENT';
    protected $_is_publication_enabled = false;
    
    // Others
    private $_backend_model = null;
    
    public function __construct()
    {
        // Add common properties
        $this->_properties['id'] = array('type' => 'string');
        $this->_properties['_rev'] = array('type' => 'string');
        
        $this->_properties['createdBy'] = array('type' => 'string');
        $this->_properties['creationDateTime'] = array('type' => 'string');
        $this->_properties['modifiedBy'] = array('type' => 'string');
        $this->_properties['lastModificationDateTime'] = array('type' => 'string');
        
        if ($this->_is_publication_enabled)
        {
            $this->_properties['public'] = array('type' => 'array');
            $this->_properties['lastPublication'] = array('type' => 'string');            
        }
    }
    
    private function _getDb()
    {
        if (!isset($this->_db))
        {
            $bd = db::getDb($this->_db_id);
            if (is_null($bd))
            {
                $this->_setDb();
                db::setDb($this->_db_id, $this->_db);
            }
            else
            {
                $this->_db = $bd;
            }
        }
        
        return $this->_db;        
    }
    
    private function _setDb()
    {
        $conn_params = config::getDBConnectionParams($this->_db_id);
        
        if(!isset($conn_params->driver->value))
        {
            throw new Exception("Model cannot be initialized, <i>'driver'</i> param missed in init.json.");
        }
        
        $driver = $conn_params->driver->value;
        
        $class_name = 'core\\model\\controller\\'.$driver;
        $this->_db = new $class_name;
        $this->_db->connect($conn_params); 
        $this->_db->setDbId($this->_db_id);
    }
    
    public function getDb()
    {
        return $this->_getDb();      
    }
    
    public function getDbId()
    {
        return $this->_db_id;      
    }
    
    public function setDbId($value)
    {
        $this->_db_id = $value;     
    }

    public function __get($property)
    {
        if (!array_key_exists($property, $this->_properties))
        {
            throw new Exception("Error getting property. It doesn't exist: ".$property);
        }

        if (!isset($this->_properties[$property]['value']))
        {
            return null;
        }
        
        return $this->_properties[$property]['value'];
    }

    public function __set($property, $value)
    {
        if (!array_key_exists($property, $this->_properties))
        {
            return;
            //throw new Exception("Error setting property. It doesn't exist: ".$property);
        }
        
        /*
         * It's due to change common documents. It has to be enabled!!!
         * 
        if ($property == 'type')
        {
            return;
            //throw new Exception("You can not set this property: ".$property);
        }
        */
        
        $this->_properties[$property]['value'] = $value;
    }
    
    public function getPublicationMode()
    {
        return $this->_publication_mode;
    }
    
    public function isPublicationEnabled()
    {
        return $this->_is_publication_enabled;
    }
    
    public function updateDataView($name, $type)
    {
        /*
        $driver = $this->_getDb()->getDriver();
        if ($driver !== 'couchbase')
        {
            return false;
        }
        */
        
        // Call the view to refresh the data with stale=false
        $arr1 = $this->getDataView($name, $type, false);
        if ($this->_is_publication_enabled && $this->_publication_mode === 'OTHER_DOCUMENT')
        {
            $arr2 = $this->getDataView('public-'.$name, 'public-'.$type, false);
        }
        
        return true;
    }
    
    public function getDataView($name, $type, $stale = 'update_after', $map = null, $params = array())
    {
        return $this->_getDb()->getDataView($name, $type, $stale, $map, $params);
    } 
    
    public function get($id)
    {
        return $this->_getDb()->get($id);
    }
    
    public function save($exists = null)
    {
        if (is_null($this->_properties['code']['value']) || empty($this->_properties['code']['value']))
        {
            throw new Exception("<i>CODE</i> property is mandatory.");
        } 
        
        $new_id = $this->getNewId();
        if (!isset($this->_properties['id']['value']) || empty($this->_properties['id']['value']))
        {
            $this->_properties['id']['value'] = $new_id;
        }
        else
        {
            if ($this->_properties['id']['value'] !== $new_id)
            {
                $this->delete($this->_properties['id']['value']);
            }            
        }
        
        $logged_user = session::getLoggedUser();
        $dateformat = config::getConfigParam(array("application", "dateformat_database"))->value;
        $timeformat = config::getConfigParam(array("application", "timeformat"))->value;
        $datetimeformat = $dateformat." ".$timeformat;
        
        $exists = ($exists !== null)? $exists : $this->exists();
        if ($exists)
        {
            $this->_properties['modifiedBy']['value'] = !$logged_user? 'script' : $logged_user->code;
            $this->_properties['lastModificationDateTime']['value'] = date($datetimeformat);                
        }
        else
        {
            $this->_properties['createdBy']['value'] = !$logged_user? 'script' : $logged_user->code;
            $this->_properties['creationDateTime']['value'] = date($datetimeformat);            
        }
        
        $object = array();
        foreach ($this->_properties as $key => $values) 
        {
            $object[$key] = (isset($values['value']))? $values['value'] : null;
        }
        
        if ($this->_is_publication_enabled && $this->_publication_mode === 'OTHER_DOCUMENT')
        {
            unset($object['public']);
        }         
        
        $object['id'] = $new_id;

        if ($exists)
        {        
            $this->update($this->_properties['id']['value'], $object);
        }
        else
        {
            $this->insert($new_id, $object);
            $this->_properties['id']['value'] = $new_id;
        }
    }
    
    public function update($id, $object)
    {
        $ret = $this->_getDb()->update($id, $object);
        $this->_updateRevProperty($ret);
        return $ret;
    }
    
    public function insert($id, $object)
    {
        $ret = $this->_getDb()->insert($id, $object);
        $this->_updateRevProperty($ret);
        return $ret;
    }
    
    private function _updateRevProperty($array)
    {
        if (isset($array) && $array[1])
        {
            $this->_properties['_rev']['value'] = $array[1];
        }        
    }
    
    public function exists($id = null)
    {
        if (is_null($id))
        {
            if (!isset($this->_properties['id']['value']) || empty($this->_properties['id']['value']))
            {
                return false;
            }
            $id = $this->_properties['id']['value'];
        }
        
        return $this->_getDb()->exists($id);
    }
    
    public function delete()
    {
        if (is_null($this->_properties['id']['value']) || empty($this->_properties['id']['value']))
        {
            throw new Exception("<i>ID</i> property is not defined.");
        }
        
        $this->_getDb()->delete($this->_properties['id']['value']);
        if ($this->_is_publication_enabled && $this->_publication_mode === 'OTHER_DOCUMENT')
        {
            $id = 'public-'.$this->_properties['id']['value'];
            if($this->exists($id))
            {
                $this->_getDb()->delete($id);
            }
        }
    }
    
    public function publish($exists = null)
    {
        if (!$this->_is_publication_enabled)
        {
            return true;
        }
        
        if (is_null($this->_properties['id']['value']) || empty($this->_properties['id']['value']))
        {
            throw new Exception("<i>ID</i> property is not defined.");
            return false;
        }
        
        if ($this->_publication_mode === 'SAME_DOCUMENT')
        {
            $id = $this->_properties['id']['value'];
            $exists = ($exists !== null)? $exists : $this->exists();
            if (!$exists)
            {
                throw new Exception("<i>ID</i> doesn't exist.");
                return false;
            }            
        }
        else
        {
            $id = 'public-'.$this->_properties['id']['value'];
            $exists = $this->exists($id);
        }
        
        $object = array();
        foreach ($this->_properties as $key => $values) 
        {
            if ($key === 'public' || 
                $key === 'lastPublication')
            {
                continue;
            }
            
            if (!isset($values['value']))
            {
                continue;
            }
            
            $object[$key] = $values['value'];            
        }
        
        $object['lastPublication'] = date("d-m-Y H:i:s");
        
        if ($this->_publication_mode === 'SAME_DOCUMENT')
        {
            $object['public'] = $object;
        }
        else
        {
            /*
            // Update no public document
            if($this->exists($this->_properties['id']['value']))
            {
                $this->update($this->_properties['id']['value'], $object);
            }
            else
            {
                $this->insert($this->_properties['id']['value'], $object);
            }
             */
            $object['id'] = $id;
            $object['type'] = 'public-'.$object['type'];
            if ($exists)
            {
                $object['_rev'] = $this->get($id)->_rev;
            }
        }

        // Update public data (or public document)
        if ($exists)
        {
            $this->update($id, $object);
        }
        else
        {
            $this->insert($id, $object);
        }
    }    
    
    public function getStorage()
    {
        $ret = array();
        
        foreach ($this->_properties as $key => $values) 
        {
            $ret[$key] = (isset($values['value']))? $values['value'] : null;
        }
        
        return helpers::objectize($ret);
    }
    
    public function loadData($id = null)
    {
        if (is_null($id) || empty($id))
        {
            return null;
        }
        
        $id = strtolower($id);
        $doc = $this->get($id);
        if (is_null($doc))
        {
            return null;
        }
        
        foreach (get_object_vars($doc) as $key => $value)
        {
            if (array_key_exists($key, $this->_properties))
            {
                $this->_properties[$key]['value'] = $value;
            }
        }
        
        return $this->getStorage();
    }
    
    public function getKeys()
    {       
        return $this->_id_COMPOSITION;
    }
    
    public function getNewId()
    {
        $id_composition = $this->_id_COMPOSITION;
        $id = '';
        foreach ($id_composition as $key => $property) {
            if ($key > 0)
            {
                $id .= '-';
            }
            $id .= strtolower($this->_properties[$property]['value']);
        }        
        return $id;
    }
    
    public function setNewId()
    {      
        $this->_properties['id']['value'] = $this->getNewId();
    }
    
    public function getBackendModel()
    {
        if (!isset($this->_backend_model))
        {
            $this->setBackendModel();
        }
        return $this->_backend_model;
    }
    
    public function setBackendModel()
    {
        $ret = array();
        $i = 0;
        
        foreach ($this->_properties as $key => $values) 
        {
            $ret[$i]['name'] = $key;
            if (isset($values['type']))
            {
                if ($values['type'] === 'date')
                {
                    $ret[$i]['dateFormat'] = config::getConfigParam(array("application", "dateformat_database"))->value;
                }
                /*if ($values['type'] !== 'array' && $values['type'] !== 'password')
                {
                    $ret[$i]['type'] = $values['type'];
                }*/
                else
                {
                    $ret[$i]['type'] = $values['type'];
                }
            }
            if (isset($values['defaultValue']))
            {
                $ret[$i]['default_value'] = $values['defaultValue'];                    
            }            
            $i++;              
        }
        
        $this->_backend_model = $ret;
    }
    
    public function replicate($rsource_conn_params, $rtarget_conn_params, $continous = false, $invert_source = false)
    {
        return $this->_getDb()->replicate($rsource_conn_params, $rtarget_conn_params, $continous = false, $invert_source = false);
    }
}