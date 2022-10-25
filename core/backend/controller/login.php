<?php

namespace core\backend\controller;

// Controllers
use core\ajax\controller\ajax;
use core\backend\controller\lang;
use core\backend\controller\session;
use core\backend\controller\backend;

// Models
use core\backend\model\user;

/**
 * Login controller
 *
 * @author Dani Gilabert
 */
class login extends backend
{
    
    public function getLanguages()
    {
        $langs = lang::getSupportedLanguages();
        ajax::sendData($langs);
    }
    
    public function checkLogin($params)
    {
        $this->setView();
        $data = array(
            'app_code' => $this->view->app_code,
            'app_title' => $this->view->app_title,
            'app_version' => $this->view->app_version,
            'app_base_path' => $this->view->app_base_path,
            'app_path_logo' => $this->view->app_logo->client_path,
            'app_width_logo' => $this->view->app_logo->width,
            'app_height_logo' => $this->view->app_logo->height,
            'app_dateformat' => $this->view->app_dateformat,
            'app_dateformat_database' => $this->view->app_dateformat_database,
            'app_decimal_separator' => $this->view->app_decimal_separator,
            'app_erp_interface_description' => $this->view->app_erp_interface_description,
            'logged_user' => $this->view->logged_user,
            'logged_lang' => $this->view->logged_lang,
            'logged_full_name_user' => $this->view->logged_full_name_user,
            'is_super_user' => $this->view->is_super_user,
            'filemanager_path' => $this->view->filemanager_path,
            'ecommerce_erp_interface_code' => $this->view->ecommerce_erp_interface_code,
            'ecommerce_vat_is_always_inclued_to_cost_price' => $this->view->ecommerce_vat_is_always_inclued_to_cost_price,
            'ecommerce_only_one_delegation' => $this->view->ecommerce_only_one_delegation
        );
        
        // Check for default admin user and create it when doesn't exist
        $this->_checkAdminUser();
        
        $user = session::getLoggedUser();
        if ($user)
        {
            if (!$user->superUser &&
               (!$user->available || !$user->anyPermission()))
            {
                session::setLogout();
                $data['success'] = false;
                $data['msg'] = '';                
                ajax::ohYeah($data);
                return;
            }
        }   
        else
        {
            if(!isset($params->user))
            {
                $msg = $this->trans('login_failed');
                $data['success'] = false;
                $data['msg'] = $msg;                
                ajax::ohYeah($data);
                return;
            }

            $user = new user("admin-user-".$params->user);
            
            $params_password = md5($params->password);
            if(!$user->exists() || 
               $user->password != $params_password)
            {
                $msg = $this->trans('login_failed');
                $data['success'] = false;
                $data['msg'] = $msg;                
                ajax::ohYeah($data);
                return;
            }
            
            if (!$user->superUser &&
               (!$user->available || !$user->anyPermission()))
            {
                $msg = $this->trans('login_not_available_user');
                $data['success'] = false;
                $data['msg'] = $msg;                
                ajax::ohYeah($data);
                return;
            }

            session::setLogin($user, $params);   
        }
                   
        $data['success'] = true;
        $data['msg'] = '';
        if (isset($params->lang))
        {
            $data['logged_lang'] = $params->lang;
        }
        $data['logged_full_name_user'] = $user->getFullName();
        $data['is_super_user'] = (($user->superUser)? 1 : 0);

        ajax::ohYeah($data);
    }
    
    public function setLogout()
    {
        session::setLogout();
        ajax::ohYeah();
    }
    
    private function _checkAdminUser()
    {
        // Check for default admin user and create it when doesn't exist
        $user = new user('admin-user-admin');
        $exist = $user->exists();
        /******** Test ********/
//        $exist = false;
//        $test = md5("admin");
//        die($test);
        /******** Test ********/
        if(!$exist)
        {
            $user->code = "admin";
            $user->password = '21232f297a57a5a743894a0e4a801fc3';
            $user->firstName ="Super";
            $user->lastName = "User";
            $user->available = true;
            $user->superUser = true;
            $user->save();
        }        
    }
}