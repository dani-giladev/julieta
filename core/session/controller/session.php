<?php

namespace core\session\controller;

// Controllers
use core\config\controller\config as config;

/**
 * Handle global session
 *
 * @author Dani Gilabert
 */
class session
{
    public static function startSession($sid = null)
    {
        $is_session_active = self::isSessionActive();
        
        if (!$is_session_active)
        {
            $expiration_hours = config::getConfigParam(array("application", "session_expiration"))->value;
            $expiration_seconds = 60 * 60 * $expiration_hours;        
            ini_set('session.gc_maxlifetime', $expiration_seconds);
            ini_set('session.cookie_lifetime', $expiration_seconds);  
        }
        
        if (is_null($sid))
        {
            if (!$is_session_active)
            {
                session_start();
            }
        }
        else
        {
            session_id($sid);
            session_start();            
        }      
    }
    
    public static function isSessionActive() {
        return (session_status() === PHP_SESSION_ACTIVE);
    }
    
    public static function killSession()
    {
	session_unset();
	session_destroy();           
    }
    
    public static function getSessionVar($key)
    {
        $cookie = self::getCookie($key);
        if (isset($cookie))
        {
            return $cookie;
        }   
        
        if(isset($_SESSION[$key]))
        {
            return $_SESSION[$key];
        }
        else
        {
            return null;
        }
    }
    
    public static function setSessionVar($key, $value)
    {
        $_SESSION[$key] = $value;
    }
    
    public static function getCookie($key)
    {
        $cookie = filter_input(INPUT_COOKIE, $key, FILTER_SANITIZE_STRING);
        return $cookie;
    }    
    
    public static function setCookie($key, $value)
    {
        $expiration_hours = config::getConfigParam(array("application", "session_expiration"))->value;
        $expiration_time = time() + 60 * 60 * $expiration_hours;
        setcookie($key, $value, $expiration_time);
    }
    
    public static function killCookie($key)
    {
        $cookie = self::getCookie($key);
        if (isset($cookie))
        {
            unset($_COOKIE[$key]);
            setcookie($key, '', time() - 3600); // empty value and old timestamp              
        }
    }
    
}
