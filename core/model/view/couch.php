<?php

namespace core\model\view;

// Controllers
use core\helpers\controller\helpers;

/**
 * Couch common view (Couchbase and couchdb)
 *
 * @author Dani Gilabert
 * 
 */
class couch
{
    protected $_name;
    protected $_type;
    protected $_keys;
    protected $_model;
    
    public function getName()
    {
        return $this->_name;
    }
    public function setName($value)
    {
        $this->_name = $value;
    }
    
    public function getType()
    {
        return $this->_type;
    }
    public function setType($value)
    {
        $this->_type = $value;
    }    
    
    public function getKeys()
    {
        return $this->_keys;
    }
    public function setKeys($value)
    {
        $this->_keys = $value;
    }    
    
    public function getModel()
    {
        return $this->_model;
    }
    public function setModel($value)
    {
        $this->_model = $value;
    }
    
    protected function _getMapView()
    {
        $map =  $this->getBasicMapViewWithKeys();    
        return $map;
    }
    
    public function getBasicMapView()
    {
        $name = $this->getName();
        $type = $this->getType();
        
        $map =  '{'.
                    '"views":{'.
                        '"'.$name.'":{'.
                            '"map":"function (doc) { '.
                                'if (doc.type == \''.$type.'\') {'.
                                    'emit(doc.code, doc); '.
                                '}'.                    
                            '}"'.
                        '}'.
                    '}'.
                '}';      
        
        return $map;
    }   
    
    public function getBasicMapViewWithKeys()
    {
        $name = $this->getName();
        $type = $this->getType();
        $keys = $this->getKeys();
        
        $map =  '{'.
                    '"views":{'.
                        '"'.$name.'":{'.
                            '"map":"function (doc) { '.
                                'if (doc.type == \''.$type.'\') {'.
                                    'emit('.
                                    '[';
                                        foreach ($keys as $key => $value) {
                                            if ($key > 0)
                                            {
                                                $map .= ', ';
                                            }
                                            $map .= $value;
                                        }
        $map .=                     '], doc); '.
                                '}'.                    
                            '}"'.
                        '}'.
                    '}'.
                '}';        
        
        return $map;
    }
    
    public function getDataView($params, $stale)
    {
        $data = $this->_model->getDataView($this->getName(), $this->getType(), $stale, $this->_getMapView(), $params);
        $ret = helpers::objectize($data); 
        return $ret;
    }
    
}