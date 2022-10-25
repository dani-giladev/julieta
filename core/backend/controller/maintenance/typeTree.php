<?php

namespace core\backend\controller\maintenance;

// Controllers
use core\backend\controller\backend;
use core\backend\controller\maintenance\data;
use core\ajax\controller\ajax;

/**
 * Maintenance tree controller
 *
 * @author Dani Gilabert
 * 
 */
class typeTree extends backend
{ 
    
    public function getTree($data)
    {
        $json = null;
        $dc = new data($data);
        
        // Get model
        $model = $dc->getModel();
        if ($model === false)
        {
            $msg = "The model is not defined";
            ajax::fuckYou($msg);
            return;                
        }    
        $type = $model->type;
        $code = $model->code;
        $record_id = $type.'-'.$code;
        $model->loadData($record_id); 
        
        $exists = false;
        if($model->exists())
        {
            // Get tree
            $tree = $model->tree;     
            if (isset($tree[0]))
            {
                if (isset($data->record_id))
                {
                    $this->_addCheckedProperties($tree, $data->record_id, $data->module_id, $data->record_model_id, $data->record_property_name);
                }
                else
                {
                    // Se expanded=false on first level
                    $first_level_children = $tree[0]->children;
                    foreach ($first_level_children as $key => $value)
                    {
                        $tree[0]->children[$key]->expanded = false;              
                    }                      
                }
                $json = '['.json_encode($tree[0]).']'; 
                $exists = true;
            }
        }
        
        if (!$exists)
        {
            $root_node_id = 'tree-root';
            $root_node_code = 'root';
            $root_node_name = $this->trans('root');
            $_data = array();
            $_data['id'] = $root_node_id;
            $_data['code'] = $root_node_code;
            $_data['name'] = $root_node_name;
            $_data['available'] = true;
            $json_data = json_encode($_data);

            $json = 
                '[
                    {
                        "id": "'.$root_node_id.'",
                        "expanded": true,
                        "parentId": "root",                        
                        "text": "'.$root_node_name.'",
                        "_data": '.$json_data.',
                        "children": []
                    }
                ]';             
        }
        
        echo ($json);        
    }
    
    public function saveTree($data)
    {
        $dc = new data($data);
        
        // Clean data. Only records
        $dc->clean();        

        // Get model
        $model = $dc->getModel();
        if ($model === false)
        {
            $msg = "The model is not defined";
            ajax::fuckYou($msg);
            return;                
        }    
        $type = $model->type;
        $code = $model->code;
        $record_id = $type.'-'.$code;
        $model->loadData($record_id); 

        // Get cleaned data
        $cleaned_data = $dc->getData();
        
        // Get tree
        $tree = array();
        $decoded_tree = json_decode($cleaned_data->tree);
        if (empty($decoded_tree))
        {
            ajax::fuckYou('Uppss! The tree is empty!');
            return;
        }
        $tree[] = $decoded_tree;
        
        // Clean true
        $this->cleanTree($tree[0]->children);
        
        // Get data of each category
        $categories = array();
        $this->addCategories($tree[0]->children, $categories);
        
        // Get tree of each category
        $subcategories = array();
        $this->addSubcategories($tree[0]->children, $subcategories);
        
        // Get bread scrumbs of each category
        $breadcrumbs = array();
        $parents = array();
        $this->addBreadcrumbs($tree[0]->children, $breadcrumbs, $parents);
        
        // Set properties
        $model->tree = $tree;
        $model->categories = $categories;
        $model->subcategories = $subcategories;
        $model->breadcrumbs = $breadcrumbs;
        
        // Save
        $model->save();
        
        if ($dc->getPublish())
        {
            // Publish
            $model->publish();            
        }

        ajax::ohYeah();
    }
    
    public function publishTree($data)
    {
        $dc = new data($data);
        
        // Get model
        $model = $dc->getModel();
        if ($model === false)
        {
            $msg = "The model is not defined";
            ajax::fuckYou($msg);
            return;                
        }    
        $type = $model->type;
        $code = $model->code;
        $record_id = $type.'-'.$code;
        $model->loadData($record_id); 

        // Check if exist
        if(!$model->exists())
        {
            $msg = "The record or file with id: '".$record_id."' does not exists";
            ajax::fuckYou($msg);
            return;
        }
        
        // Publish
        $model->publish();       

        ajax::ohYeah();
    }
            
    private function _addCheckedProperties(&$tree, $record_id, $module_id, $record_model_id, $record_property_name)
    {
        // Get model record
        $class_name = 'modules\\'.$module_id.'\\model\\'.$record_model_id;
        if (!class_exists($class_name))
        {
            return;            
        }            
        $model = new $class_name($record_id);
        if ($model === false)
        {
            return;                
        }          

        // Get property
        $record_property_value = $model->$record_property_name;
        $haystack = explode('|', $record_property_value);
        
        $this->_updateChildWithCheckedProperties($tree, $haystack);
    }
    
    private function _updateChildWithCheckedProperties(&$tree, $haystack)
    {
        foreach ($tree as $key => $value)
        {
            if ($value->id !== 'tree-root')
            {
                if (in_array($value->_data->code, $haystack))
                {
                    $tree[$key]->checked = true;          
                    $tree[$key]->expanded = true;
                }            
                else
                {
                    $tree[$key]->checked = false;          
                    $tree[$key]->expanded = false;
                }
            }

            if (isset($value->children))
            {
                self::_updateChildWithCheckedProperties($value->children, $haystack);
            }               
        }  
    }
    
    public function addCategories($tree, &$categories)
    {
        foreach ($tree as $key => $value)
        {
            $code = $value->_data->code;
            $categories[$code] = $value->_data;
            if (isset($value->children))
            {
                self::addCategories($value->children, $categories);
            }
        }
    }
    
    public function addSubcategories($tree, &$subcategories)
    {
        foreach ($tree as $key => $value)
        {
            if (isset($value->children))
            {
                $code = $value->_data->code;
                $subcategories[$code] = $value->children;
                self::addSubcategories($value->children, $subcategories);
            }
        }
    }
    
    public function addBreadcrumbs($tree, &$breadcrumbs, &$parents)
    {
        foreach ($tree as $key => $value)
        {
            $parents[] = $value->_data;
            
            $code = $value->_data->code;
            $breadcrumbs[$code] = $parents;
            
            if (isset($value->children) && !empty($value->children))
            {
                $this->addBreadcrumbs($value->children, $breadcrumbs, $parents);
            }
            array_pop($parents);
        }
    }
    
    public function cleanTree(&$tree)
    {
        foreach ($tree as $key => $value)
        {
            $new_data = new \stdClass();
            $data = $tree[$key]->_data;
            foreach ($data as $k => $v) {
                if (is_bool($v) || !empty($v))
                {
                    $new_data->$k = $v;
                }
            }
            $tree[$key]->_data = $new_data;
            
            // Fix extjs6
            if ($tree[$key]->leaf)
            {
                $tree[$key]->leaf = false;
                $tree[$key]->children = [];
                $tree[$key]->expanded = true;
            }
                
            if (isset($value->children) && !empty($value->children))
            {
                $this->cleanTree($tree[$key]->children);
            }
        }
    }
    
}