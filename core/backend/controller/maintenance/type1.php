<?php

namespace core\backend\controller\maintenance;

// Controllers
use core\config\controller\config;
use core\ajax\controller\ajax;
use core\backend\controller\backend;
use core\backend\controller\session;
use core\backend\controller\maintenance\data;

/**
 * Maintenance controller (Type 1)
 *
 * @author Dani Gilabert
 * 
 */
class type1 extends backend
{
    
    public function getDc($data)
    {
        $dc = new data($data);
        return $dc;
    }
    
    public function getRecords($data, $send_data = true)
    {
        $dc = new data($data);

        // Get model
        $model = $dc->getModel();
        if ($model === false)
        {
            $msg = "The model is not defined";
            if ($send_data) ajax::fuckYou($msg);
            return false;
        }    
        $model_type = $model->type;

        // Get data
        $stale = $dc->getStale();
        $object = $model->getDataView($model_type, $model_type, $stale);
        
        // Filtering
        $dc->filtering($object);
        
        // Add data
        $dc->addData($object);
        
        // Discard fields
        $dc->discardFields($object);
        
        // Concrete fields
        if ($dc->isConcreteFields())
        {
            $ret = array();
            if (isset($object->rows) && !empty($object->rows))
            {
                foreach ($object->rows as $row) 
                {
                    $obj = $row->value;
                    $obj = $dc->getConcreteFields($obj);
                    $ret[] = $obj;
                }
            }
        }
        else
        {
            $ret = $object;
        }
        
        if ($send_data)
        {
            ajax::sendData($ret); 
        }
        else
        {
            return $ret;
        }
    }
    
    public function getRecord($params, $send_data = true)
    {
        $dc = new data($params);
        
        // Clean data. Only records
        $dc->clean();
                
        // Get model
        $record_id = $dc->getRecordId();
        $model = $dc->getModel($record_id);
        if ($model === false)
        {
            $msg = "The model is not defined";
            if ($send_data)
            {
                ajax::fuckYou($msg);
                return;
            }
            return array(
                'success' => false,
                'msg' => $msg,
                'data' => null
            );
        }
        
        $data = $model->getStorage();
             
        // Build data model
        $data_model = array();
        foreach ($data as $key => $value) {
            $data_model[] = array(
                'name' => $key
            );
        }
        
        $ret =  array(
            'data' => $data,            
            'model' => $data_model
        );
        
        ajax::ohYeah($ret);
    }
    
    public function saveRecord($data, $send_data = true)
    {
        $dc = new data($data);
        
        // Clean data. Only records
        $dc->clean();

        // Get model
        $record_id = $dc->getRecordId();
        $model = $dc->getModel($record_id);
        if ($model === false)
        {
            $msg = "The model is not defined";
            if ($send_data)
            {
                ajax::fuckYou($msg);
                return;
            }
            return array(
                'success' => false,
                'msg' => $msg,
                'data' => null
            );
        }
        
        // Set properties
        $cleaned_data = $dc->getData();
        $is_new_record = $dc->getIsNewRecord();
        $this->_setPropertiesOnSave($cleaned_data, $model, $is_new_record);
        
        // Is record duplicate?
        $is_record_duplicate = $this->_isRecordDuplicate($model, $record_id, $is_new_record);
        if($is_record_duplicate)
        {
            $msg = $this->_getMsgErrorOnSave($model, $cleaned_data, $dc->getModuleId());
            if ($send_data)
            {
                ajax::fuckYou($msg);
                return;
            }
            return array(
                'success' => false,
                'msg' => $msg,
                'data' => null
            );
        }        
            
        // Save
        $model->save();
        
        if ($dc->getPublish())
        {
            // Publish
            $model->publish();            
        }
        
        // Refresh the data view
        $model_type = $model->type;
        $model->updateDataView($model_type, $model_type);
        
        // Send the updated record
        $record = (array) $model->getStorage();
        // Discard fields before sending
        $dc->discardRecordFields($record);
        
        if ($send_data)
        {
            ajax::ohYeah($record);
        }
        else
        {
            return array(
                'success' => true,
                'msg' => '',
                'data' => $record
            );
        }
    }
    
    public function deleteRecord($data)
    {
        $dc = new data($data);
        
        // Get model
        $record_id = $dc->getRecordId();
        $model = $dc->getModel($record_id);
        if ($model === false)
        {
            $msg = "The model is not defined";
            ajax::fuckYou($msg);
            return;                
        }

        // Check if exist
        if(!$model->exists())
        {
            $msg = "The record or file with id: '".$record_id."' does not exists";
            ajax::fuckYou($msg);
            return;
        }
        
        // Delete
        $model->delete();
        
        // Refresh the data view
        $model_type = $model->type;
        $model->updateDataView($model_type, $model_type);

        ajax::ohYeah();
    }
    
    public function exportRecords($params)
    {
        $dc = new data($params);
        
        // Get model
        $model = $dc->getModel();
        if ($model === false)
        {
            $msg = "The model is not defined";
            ajax::fuckYou($msg);
            return;                
        }    
        $model_type = $model->type;

        // Get data
        $stale = $dc->getStale();
        $object = $model->getDataView($model_type, $model_type, $stale);
        
        $data = $this->getDataToExportRecords($object);
        $data_file = '';
        if (!empty($data))
        {
            $model_properties = $model->getBackendModel();
            $data_file = $this->getDataFileToExportRecords($data, $model_properties);
        }
        
        header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");  
        header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");  
        header ("Cache-Control: no-cache, must-revalidate");  
        header ("Pragma: no-cache");  
        header ("Content-type: application/vnd.ms-excel");
        header ("Content-Disposition: attachment; filename=\"".$model_type."-list.csv\"" );
        
        echo $data_file;               
    }
    
    public function getDataToExportRecords($object)
    {
        $data = array();
        
        foreach ($object->rows as $row) {
            $values = $row->value;
            unset($values->_id);
            unset($values->_rev);
            unset($values->id);
            unset($values->_conflicts);
            unset($values->_deleted_conflicts);
            unset($values->public);
            unset($values->type);
            $data[] = $values;
        }
        
        return $data;        
    }
    
    public function getDataFileToExportRecords($data, $model_properties)
    {
        $data_file = '';
        
        $lang = session::getSessionVar('loginLang');
        $default_lang = config::getConfigParam(array("application", "default_language"))->value;

        // Header
        foreach ($model_properties as $values)
        {
            if ($values['name'] === '_id') continue;
            if ($values['name'] === '_rev') continue;
            if ($values['name'] === 'id') continue;
            if ($values['name'] === 'public') continue;
            if ($values['name'] === 'type') continue;
            $data_file .= "\"".$values['name']."\";";
        }
        $data_file .= PHP_EOL; 

        // Content
        foreach ($data as $data_values)
        {
            foreach ($model_properties as $values)
            {
                if ($values['name'] === '_id') continue;
                if ($values['name'] === '_rev') continue;
                if ($values['name'] === 'id') continue;
                if ($values['name'] === 'public') continue;
                if ($values['name'] === 'type') continue;
                $name = $values['name'];
                $type = (isset($values['type']))? $values['type'] : 'string';

                if (isset($data_values->$name))
                {
                    $value = $data_values->$name;
                    if ($type === 'boolean')
                    {
                        $val = $value? 'SI' : 'NO';
                    }
                    elseif ($type === 'array')
                    {
                        if (is_object($value))
                        {
                            $val = '';
                            if (isset($value->$lang) && !empty($value->$lang))
                            {
                                if (!is_object($value->$lang))
                                {
                                    $val = $value->$lang;
                                }                                      
                            }
                            else
                            {
                                if (isset($value->$default_lang) && !empty($value->$default_lang))
                                {
                                    if (!is_object($value->$default_lang))
                                    {
                                        $val = $value->$default_lang;
                                    }
                                }
                            }
                        }
                        else
                        {
                            $val = $value;   
                        }
                    }
                    else
                    {
                        // string
                        $val = $value;
                    }
                }
                else
                {
                    $val = '';
                }

                if (is_object($val) || is_array($val))
                {
                    $val = '';
                }

                $val = preg_replace("/<br\W*?\/>/", "\n", $val);
                $val = strip_tags($val);
                $data_file .= "\"".$val."\";";
            }                      
            $data_file .= PHP_EOL; 
        }
        
        return $data_file;
    }
    
    public function publishRecord($data)
    {
        $dc = new data($data);
        
        // Get model
        $record_id = $dc->getRecordId();
        $model = $dc->getModel($record_id);
        if ($model === false)
        {
            $msg = "The model is not defined";
            ajax::fuckYou($msg);
            return;                
        }

        // Check if exist
        if(!$model->exists())
        {
            $msg = "The record or file with id: '".$record_id."' does not exists";
            ajax::fuckYou($msg);
            return;
        }
        
        // Publish
        $model->publish();    

        // Refresh the data view
        $model_type = $model->type;
        $model->updateDataView($model_type, $model_type);
        
        ajax::ohYeah();
    }
    
    public function publishAllRecords($data)
    {
        $dc = new data($data);
        
        // Get class name
        $msg = '';
        $class_name = $dc->getClassModelName($msg);
        if ($class_name === false)
        {
            ajax::fuckYou($msg);
            return;                
        }
        
        // Records id to publish
        $records_id = $dc->getRecordsId();
        
        foreach ($records_id as $id) {
            $model = new $class_name($id);

            if(!$model->exists())
            {
                $msg = "The record or file with id: '".$id."' does not exists";
                ajax::fuckYou($msg);
                return;
            }

            // Publish
            $model->publish();            
        }     

        // Refresh the data view
        $model_type = $model->type;
        $model->updateDataView($model_type, $model_type);
        
        ajax::ohYeah();
    }
    
    public function cloneRecord($data, $send_data = true)
    {
        $dc = new data($data);
        
        // Clean data. Only records
        $dc->clean();

        // Get model
        $record_id = $dc->getRecordId();
        $model = $dc->getModel($record_id);
        if ($model === false)
        {
            $msg = "The model is not defined";
            if ($send_data)
            {
                ajax::fuckYou($msg);
                return;
            }
            return array(
                'success' => false,
                'msg' => $msg,
                'data' => null
            );
        }
        
        // Check if exist the current record
        if (!$model->exists())
        {
            $msg = "The record or file with id: '".$record_id."' does not exists";
            if ($send_data)
            {
                ajax::fuckYou($msg);
                return;
            }
            return array(
                'success' => false,
                'msg' => $msg,
                'data' => null
            );
        }
        
        // Set properties
        $cleaned_data = $dc->getData();
        $this->_setPropertiesOnSave($cleaned_data, $model, true);
        
        // Is record duplicate?
        $is_record_duplicate = $this->_isRecordDuplicate($model, $record_id, true);
        if($is_record_duplicate)
        {
            $msg = $this->_getMsgErrorOnSave($model, $cleaned_data, $dc->getModuleId());
            if ($send_data)
            {
                ajax::fuckYou($msg);
                return;
            }
            return array(
                'success' => false,
                'msg' => $msg,
                'data' => null
            );
        } 
        
        // Save
        $model->save();

        // Refresh the data view
        $model_type = $model->type;
        $model->updateDataView($model_type, $model_type);
        
        // Get the updated record
        $record = (array) $model->getStorage();
        
        if ($send_data)
        {
            ajax::ohYeah();
        }
        else
        {
            return array(
                'success' => true,
                'msg' => '',
                'data' => $record
            );
        }
    }
    
    public function saveProperty($data)
    {
        $dc = new data($data);
        
        // Clean data. Only records
        $dc->clean();

        // Get model
        $record_id = $dc->getRecordId();
        $model = $dc->getModel($record_id);
        if ($model === false)
        {
            $msg = "The model is not defined";
            ajax::fuckYou($msg);
            return;                
        }
        
        // Set property
        $cleaned_data = $dc->getData();
        $property_name = $cleaned_data->property_name;
        $model->$property_name = $cleaned_data->property_value;
            
        // Save
        $model->save();
        
        if ($dc->getPublish())
        {
            // Publish
            $model->publish();            
        }

        // Refresh the data view
        $model_type = $model->type;
        $model->updateDataView($model_type, $model_type);

        ajax::ohYeah();
    }
            
    private function _getValueTypeBoolean($value)
    {
        $ret = (boolean) $value;
        return $ret;
    }
            
    private function _getValueTypePassword($value, $is_new_record, $name, $model)
    {
        $ret = null;
        
        if ($is_new_record)
        {
            $ret = md5($value);
        }
        else
        {
//            $ucKey = ucfirst($name); ;
//            $getProperty = 'get'.$ucKey;
//            $existent_value = $model->$getProperty();
            $existent_value = $model->$name;  
            if ($value === $existent_value)
            {
                $ret = $value;
            }
            else
            {
                $ret = md5($value);
            }
        }
        
        return $ret;
    }
            
    private function _getValueTypeArray($values)
    {
        //$json_records = str_replace('&#34;', '"', $values);
        $json_records = html_entity_decode($values, ENT_QUOTES);
        $records = json_decode($json_records, true);     
        
        return $records;
    }
    
    private function _setPropertiesOnSave(&$data, &$model, $is_new_record)
    {
        // Get the server model properties
        $model_properties = $model->getBackendModel();
        
        // Discard html code
        foreach ($data as $key => $values)
        {
            $data->$key = html_entity_decode($values, ENT_QUOTES);
        }
        
        // Add code if it does not exist
        if ($is_new_record && !isset($data->code))
        {
            $data->code = date("YmdHis")."-".rand(100, 999);
        }
                
        // Set server model properties according to the model
        foreach ($model_properties as $values)
        {
            $name = $values['name'];
            // Test
//            if ($name == 'url-ca')
//            {
//                $test = true;
//            }
            $type = (isset($values['type']))? $values['type'] : 'string';
            $default_value = (isset($values['default_value']))? $values['default_value'] : null;
            $set_property = false;
            if (isset($data->$name))
            {
                $value = $data->$name;
                switch ($type)
                {
                    case 'boolean':
                        $value = $this->_getValueTypeBoolean($value);
                        break;
                    case 'password':
                        $value = $this->_getValueTypePassword($value, $is_new_record, $name, $model);
                        break;
                    case 'array':
                        $value = $this->_getValueTypeArray($value, $is_new_record, $name, $model);
                        break;
                    default:
                        // string
                        break;
                }
                $set_property = true;                
            }
            else
            {
                if ($type === 'array' && $name !== 'public')
                {
                    $value = array();
                    foreach ($data as $key => $values)
                    {
                        $pos = strpos($key, '-');
                        if ($pos !== false)
                        {
                            $arr_key = substr($key, 0, $pos);
                            $subarr_key = substr($key, $pos+1);
                            if ($arr_key === $name)
                            {
                                $set_property = true;
                                $value[$subarr_key] = $values;
                                continue;
                            }
                        }
                    }                     
                }
                else
                {
                    if (isset($default_value))
                    {
                        $value = $default_value;
                        $set_property = true;
                    }                    
                }
            }
                    
            if ($set_property)
            {
                $model->$name = $value;   
            }            
        }          
    }
            
    private function _isRecordDuplicate(&$model, $record_id, $is_new_record)
    {
        $ret = false;
        
        if($is_new_record)
        {
            $model->setNewId();
            if($model->exists())
            {
                $ret = true;
            }            
        }
        else
        {
            $new_id = $model->getNewId();
            if ($new_id !== $record_id && $model->exists($new_id))
            {
                $ret = true;
            }
        }             
        
        return $ret;   
    }
            
    private function _getMsgErrorOnSave($model, $data, $module_id)
    {
        $keys = $model->getKeys();
        if (count($keys) > 2)
        {
            $msg = $this->trans('combination_already_exists');
            $msg .= '</br></br>'.$this->trans('keys').': ';
            foreach ($keys as $value) {
                if ($value !== 'type')
                {
                    $trans = $this->trans($value, $module_id);
                    if (empty($trans)) 
                    {
                        $trans = $this->trans($value, 'core');
                        if (empty($trans)) 
                        {
                            $trans = $this->trans($value);
                        }
                    }
                    $msg .= '</br>- '.$trans.': '.$data->$value; 
                }
            }
        }
        else
        {
            $msg = $this->trans('the_record_with_code')." '".$data->code."' ".$this->trans('already_exists');                
        }        
        
        return $msg;
    }

}