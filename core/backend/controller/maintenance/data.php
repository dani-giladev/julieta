<?php

namespace core\backend\controller\maintenance;

// Controllers
use core\backend\controller\session;

/**
 * Data controller for Maintenance controller
 *
 * @author Dani Gilabert
 * 
 */
class data
{
    private $_data = null;
    private $_module_id = null;
    private $_model_id = null;
    private $_stale = null;
    private $_is_new_record = null;
    private $_record_id = null;
    private $_records_id = null;
    private $_publish = null;
    private $_filters = null;
    private $_add_data = null;
    private $_discard_fields = null;
    private $_get_concrete_fields = null;
    
    public function __construct($data)
    {
        $this->module_id = 'core';
        
        $this->_data = $data;
        $this->_setModuleId();
        $this->_setModelId();
        $this->_setStale();
        $this->_setIsNewRecord();
        $this->_setRecordId();
        $this->_setRecordsId();
        $this->_setPublish();
        $this->_setFilters();
        $this->_setAddedData();
        $this->_setDiscardFields();
        $this->_setGettingConcreteFields();
    }
    
    public function getData()
    {
        return $this->_data;
    }
    
    private function _setModuleId()
    {
        $this->_module_id = $this->_data->module_id;
    }
    
    public function getModuleId()
    {
        return $this->_module_id;
    }
    
    private function _setModelId()
    {
        $this->_model_id = $this->_data->model_id;
    }
    
    private function _setStale()
    {
        $this->_stale = (isset($this->_data->stale))? $this->_data->stale : 'update_after';        
    }
    
    public function getStale()
    {
        return $this->_stale;
    }
    
    private function _setIsNewRecord()
    {
        if (isset($this->_data->is_new_record))
        {
            $this->_is_new_record = ($this->_data->is_new_record === 'true')? true : false;               
        }
    }
    
    public function getIsNewRecord()
    {
        return $this->_is_new_record;
    }
    
    private function _setRecordId()
    {
        if (isset($this->_data->record_id))
        {
            $this->_record_id = $this->_data->record_id;  
        } 
    }
    
    public function getRecordId()
    {
        return $this->_record_id;
    }
    
    private function _setRecordsId()
    {
        if (isset($this->_data->records_id))
        {
            $json_records_id = html_entity_decode($this->_data->records_id, ENT_QUOTES);           
            $this->_records_id = json_decode($json_records_id);            
        }
    }
    
    public function getRecordsId()
    {
        return $this->_records_id;
    }
    
    private function _setPublish()
    {
        if (isset($this->_data->publish))
        {
            $this->_publish = ($this->_data->publish === 'true')? true : false;
        }
    }
    
    public function getPublish()
    {
        return $this->_publish;
    }
    
    private function _setFilters()
    {
        if (isset($this->_data->filters))
        {
            $json_filters = html_entity_decode($this->_data->filters, ENT_QUOTES);           
            $this->_filters = json_decode($json_filters);            
        }        
    }
    
    private function _setAddedData()
    {
        if (isset($this->_data->add_data))
        {
            $json_add_data = html_entity_decode($this->_data->add_data, ENT_QUOTES);           
            $this->_add_data = json_decode($json_add_data);            
        }      
    }
    
    private function _setDiscardFields()
    {
        if (isset($this->_data->discard_fields))
        {
            $json_discard_fields = html_entity_decode($this->_data->discard_fields, ENT_QUOTES);           
            $this->_discard_fields = json_decode($json_discard_fields);            
        }      
    }
    
    public function isConcreteFields()
    {
        return (isset($this->_get_concrete_fields));
    }
    
    private function _setGettingConcreteFields()
    {
        if (isset($this->_data->get_concrete_fields))
        {
            $json_get_concrete_fields = html_entity_decode($this->_data->get_concrete_fields, ENT_QUOTES);           
            $this->_get_concrete_fields = json_decode($json_get_concrete_fields);            
        }      
    }
    
    public function getModel($record_id = null)
    {      
        $class_name = $this->getClassModelName();
        if ($class_name === false)
        {
            return false;
        }
        
        if (isset($record_id) && !empty($record_id))
        {
            $model = new $class_name($record_id);
        }
        else
        {
            $model = new $class_name;
        }
        
        return $model;
    }
    
    public function getClassModelName()
    {
        $class_name = 'modules\\'.$this->_module_id.'\\model\\'.$this->_model_id;
        if (!class_exists($class_name))
        {
            return false;            
        }     
        
        return $class_name;
    }
    
    public function filtering(&$object)
    {
        if(isset($object) && isset($this->_filters) && !empty($this->_filters))
        {
            foreach($object->rows as $row_key => $row_values)
            {
                foreach($this->_filters as $filter_values)
                {
                    $field = $filter_values->field;
                    $value = $filter_values->value;
                    if(array_key_exists($field, $row_values->value))
                    {
                        $authorized = 'authorized';
                        if (strlen($value) >  strlen($authorized) && substr($value, 0, strlen($authorized)) === $authorized)
                        {
                            $super_user = session::getSessionVar("superUser");
                            if ($super_user)
                            {
                                continue;
                            }
                            $var_session_name = substr($value, strlen($authorized) + 1);
                            $var_session = session::getSessionVar($var_session_name);
                            $var_session_pieces = explode('|', $var_session);
                            if (in_array($row_values->value->$field, $var_session_pieces))
                            {
                                continue;
                            }
                        }
                        else
                        {
                            if(array_key_exists($field, $row_values->value))
                            {                            
                                if($row_values->value->$field === $value)
                                {
                                    continue;
                                } 
                            }                            
                        }
                    }                          
                    unset($object->rows[$row_key]);
                    break;
                }                
            }
        }             
    }
    
    public function addData(&$object)
    {
        if(isset($object) && isset($this->_add_data))
        {
            foreach($this->_add_data as $add_data_values)
            {
                $item = new \stdClass;
                $item->value = $add_data_values;
                $object->rows[] = $item;
            }       
        }          
    }
    
    public function discardFields(&$object)
    {
        if(isset($object))
        {
            foreach ($object->rows as $row_key => $row_values) 
            {
                // Public property will always delete
                unset($object->rows[$row_key]->value->public);
                if (isset($this->_discard_fields))
                {
                    foreach($this->_discard_fields as $discard_fields_values)
                    {
                        unset($object->rows[$row_key]->value->$discard_fields_values);
                    }                      
                }
            }            
        }            
    }
    
    public function discardRecordFields(&$record)
    {
        if (isset($record))
        {
            // Public property will always delete
            unset($record['public']);
            if (isset($this->_discard_fields))
            {
                foreach($this->_discard_fields as $discard_fields_values)
                {
                    unset($record[$discard_fields_values]);
                }                      
            }
        }            
    }
    
    public function getConcreteFields($object)
    {
        if (isset($this->_get_concrete_fields))
        {
            $ret = new \stdClass();
            foreach ($this->_get_concrete_fields as $key)
            {
                $ret->$key = $object->$key;
            }
        }
        else
        {
            $ret = $object;
        }
        
        return $ret;
    }
    
    public function clean()
    {
        // Clean data. Only records
        unset($this->_data->_dc);
        unset($this->_data->controller);
        unset($this->_data->method);
        unset($this->_data->module_id);
        unset($this->_data->model_id);
        unset($this->_data->is_new_record);
        unset($this->_data->model);
        unset($this->_data->XDEBUG_SESSION_START);       
    }

}