<?php

namespace core\backend\controller;

// Controllers
use core\ajax\controller\ajax;
use core\helpers\controller\helpers;
use core\backend\controller\session;
use core\backend\controller\backend;

/**
 * Main controller
 *
 * @author Dani Gilabert
 */
class main extends backend
{
    
    public function getModules()
    {
        $user = session::getLoggedUser();
        $modules = $user->getVisibleModules();  
        
        $arr = array();
        foreach ($modules as $module_id)
        {
            $icon_file = 'modules/'.$module_id.'/backend/res/icon';
            if (file_exists($icon_file))
            {
                $icon = file_get_contents($icon_file);
            }
            else
            {
                $icon = "x-fa fa-question";
            }
            
            $params_menu = new \stdClass();
            $params_menu->module_id = $module_id;
            $params_menu->echo = false;
            $menus = $this->getMenu($params_menu);
            
            $item = array();
            $item['module_id'] = $module_id;
            $item['icon'] = $icon;
            $item['menus'] = $menus;
            $arr[] = $item;
        }
        $ret = helpers::objectize($arr);
        ajax::sendData($ret);
    }
    
    public function getMenu($params)
    {
        $module_id = $params->module_id;
        $this->module_id = $module_id;
        $echo = (isset($params->echo))? $params->echo : true;
        
        $menu_file = 'modules/'.$module_id.'/backend/res/menu/menu.json';
        if(!file_exists($menu_file))
        {
            if ($echo)
            {
                echo json_encode(array());            
            }
            else
            {
                return new \stdClass();
            }
        }          
        
        $object_menu = json_decode(file_get_contents($menu_file));
        
        // Discard invisible menus
        $visible_items_menu = array();
        $user = session::getLoggedUser();
        if (!$user->superUser)
        {
            $visible_items_menu = $user->getVisibleItemsMenu($module_id);
        }
        
        // Discard specific items menus
        $items_menu_to_discard = array();
        
        // Discard!
        if (!empty($visible_items_menu) || !empty($items_menu_to_discard))
        {
            $object_menu = $this->_discardItemsMenu($object_menu, $visible_items_menu, $items_menu_to_discard);
        }
        
        // Translate menu texts
        $breadscrumb = $this->trans($module_id, 'core');;
        $translated_menu_object = $this->_translateMenu($module_id, $object_menu, $breadscrumb);         
        
        if ($echo)
        {
            $ret = json_encode($translated_menu_object);
            echo $ret;            
        }
        else
        {
            return $translated_menu_object;
        }

    }
    
    private function _discardItemsMenu(&$menu, $visible_items_menu, $items_menu_to_discard)
    {
        foreach ($menu as $menu_key => $menu_value)
        {
            if (isset($menu_value->alias) &&
                !isset($menu_value->children))
            {
                if 
                (
                    (!empty($visible_items_menu) && !in_array($menu_value->alias, $visible_items_menu)) || 
                    in_array($menu_value->alias, $items_menu_to_discard)
                )
                {
                    unset($menu[$menu_key]);
                }
            }
            elseif (isset($menu_value->children))
            {
                $children = self::_discardItemsMenu($menu_value->children, $visible_items_menu, $items_menu_to_discard);
                if (empty($children))
                {
                    unset($menu[$menu_key]);
                }
                else 
                {
                    $menu[$menu_key]->children = $children;          
                }
            }
        }  
        
        array_splice($menu, 0, 0);
        
        return $menu;
    }
    
    private function _translateMenu($module_id, &$menu, $breadscrumb)
    {
        foreach ($menu as $menu_key => $menu_value)
        {
            $current_breadscrumb = '';
            
            if (isset($menu_value->alias))
            {
                $trans = $this->trans($menu_value->alias.'_menu', $module_id);
                $menu[$menu_key]->label = $trans;   
            
                // Breadscrumb
                if (!empty($breadscrumb))
                {
                    $current_breadscrumb .= $breadscrumb.' > ';
                }
                $current_breadscrumb .= $trans;
                $menu[$menu_key]->breadscrumb = $current_breadscrumb;
            }
            
            if (isset($menu_value->children))
            {
                $children = self::_translateMenu($module_id, $menu_value->children, $current_breadscrumb);
                $menu[$menu_key]->children = $children;                    
            }
        }  
        
        return $menu;
    }
    
    public function setLogout()
    {
        session::setLogout();
        ajax::ohYeah();
    }
    
    // Get model and permissions of the selected menu
    public function getSelectedMenuInfo($data)
    {
        $ret = null;
            
        // Gerenal ajax properties
        $module_id = $data->module_id;
        $model_id = $data->model_id;
        $menu_id = $data->menu_id;
            
        // Get model
        $class_name = 'modules\\'.$module_id.'\\model\\'.$model_id;
        if (class_exists($class_name))
        {
//            $class_path = str_replace('\\','/',$class_name);
//            $arr['success'] = false;
//            $arr['message'] = "The model '".$class_path."' is not defined";
//            $ret[] = $arr;
//            ajax::sendData($ret);
//            return;            
            $model = new $class_name();
            // Get the server model properties
            $model_properties = $model->getBackendModel();
            foreach ($model_properties as $values)
            {
                if (isset($values['type']) && ($values['type'] === 'array' || $values['type'] === 'password'))
                {
                    unset($values['type']);
                }
                $arr['fields'][] = $values;
            }          
        }
      
        // Get permissions
        $user = session::getLoggedUser();
        $permissions = $user->getPermissions();    
        if ($permissions->$module_id->granted === 'all')
        {
            $visualize = true;
            $update = true;
            $delete = true;
            $publish = true;
        }
        elseif ($permissions->$module_id->granted === 'none')
        {
            $visualize = false;
            $update = false;
            $delete = false;
            $publish = false;
        }
        else
        {
            $visualize = $permissions->$module_id->custom->$menu_id->visualize;
            $update = $permissions->$module_id->custom->$menu_id->update;
            $delete = $permissions->$module_id->custom->$menu_id->delete;
            $publish = $permissions->$module_id->custom->$menu_id->publish;     
        }
        $arr['permissions']['visualize'] = $visualize;
        $arr['permissions']['update'] = $update;
        $arr['permissions']['delete'] = $delete;
        $arr['permissions']['publish'] = $publish;
        
        $arr['success'] = true;
        $arr['message'] = '';
        $ret[] = $arr;
        
        ajax::sendData($ret);
    }
}