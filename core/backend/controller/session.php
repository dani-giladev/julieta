<?php

namespace core\backend\controller;

// Controllers
use core\session\controller\session as coreSession;
use core\config\controller\config;
use core\backend\controller\lang;

// Models
use core\backend\model\user;

/**
 * Handle backend user's session
 *
 * @author Dani Gilabert
 */
class session extends coreSession
{
    
    public static function setLogin($user, $data)
    {
        lang::setLocales($data->lang);
        
        /*if (isset($data->rememberme) && $data->rememberme)
        {
            $cookie_key = config::getConfigParam(array("application", "title"))->value;
            $user->hidePassword();
            $user->loginLang = $data->lang;
            $cookie_value = base64_encode(serialize($user));
            self::setCookie($cookie_key, $cookie_value);
            self::startSession();
            return;
        }*/
        
        self::setSessionVar('code', $user->code);
        self::setSessionVar('loginLang', $data->lang);
        self::setSessionVar('firstName', $user->firstName);
        self::setSessionVar('lastName', $user->lastName);
        self::setSessionVar('superUser', $user->superUser);

        $app_code = config::getConfigParam(array("application", "code"))->value;
        if ($app_code === 'smartgolf')
        {
            self::setSessionVar('clubs', $user->clubs);
        }
        else
        {
            self::setSessionVar('delegations', $user->delegations);
        }        
        
        self::startSession();
    }
    
    public static function setLogout()
    {
        if(isset($_SESSION['code']))
        {
            unset($_SESSION['code']);
        }
        
        $cookie_key = config::getConfigParam(array("application", "title"))->value;        
        self::killCookie($cookie_key);
        
        self::killSession();     
        
        return true;
    }
    
    public static function getSessionVar($var)
    {
        $cookie = self::_getCookie();
        
        if($cookie)
        {
            if(get_class($cookie) == "core\backend\model\user")
            {
                $value = $cookie->$var;
                return $value;
            }
        }
        
        if(isset($_SESSION[$var]))
        {
            return $_SESSION[$var];
        }
        else
        {
            return null;
        }
    }
    
    private static function _getCookie()
    {
        $cookie_key = config::getConfigParam(array("application", "title"), false)->value;
        $cookie = self::getCookie($cookie_key);
        if (is_null($cookie))
        {
            $cookie = null;
        }
        
        $cookie_object = unserialize(base64_decode($cookie));
        
        return $cookie_object;
    }
    
    public static function getLoggedUser()
    {
        $logged_user_code = self::getSessionVar("code");

        if (is_null($logged_user_code))
        {
            return false;
        }
        else
        {
            return new user("admin-user-".$logged_user_code);
        }
    }
    
}