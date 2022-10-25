<?php

namespace core\url\controller;

/**
 * URL useful functions
 *
 * @author Dani Gilabert
 * 
 */
class url
{
    
    public static function getProtocol()
    {
        $ret = (!empty($_SERVER['HTTPS'])) ? "https://" : "http://";
        return $ret;        
    }
    
    public static function getServerName()
    {
        return $_SERVER['SERVER_NAME'];        
    }
    
    public static function getParams($request = false)
    {
        if (!$request)
        {
            return $_SERVER['QUERY_STRING'];
        }
        else
        {
            return $_REQUEST;
        }
    }
    
    public static function getCurrentUrlWithoutParams()
    {
        $path_info = (isset($_SERVER['REDIRECT_URL']))? $_SERVER['REDIRECT_URL'] : '';
        $ret = self::getProtocol().self::getServerName().$path_info;
        return $ret;        
    }
    
    public static function getCurrentUrlWithParams()
    {
        $ret =  self::getProtocol().self::getServerName().$_SERVER['REQUEST_URI'];
        return $ret;        
    }
    
    public static function updateParameters(
            $parameters_to_update = array(), $parameters_to_delete = array(), 
            $url_without_params = null, $parameters = null
    )
    {
        
        if (is_null($parameters))
        {
            $parameters = $_REQUEST;
        }
        
        unset($parameters['XDEBUG_SESSION_START']); // Clean bad parameters when debugging            
        //
        // Deleting parameters
        if (isset($parameters_to_delete) && !empty($parameters_to_delete))
        {
            foreach ($parameters_to_delete as $key) 
            {
                unset($parameters[$key]);
            }
        }
        
        // Updating parameters
        if (isset($parameters_to_update) && !empty($parameters_to_update))
        {
            foreach ($parameters_to_update as $key => $value) 
            {
                $parameters[$key] = $value;
            }
        }

        $ret = self::_buildUrl($parameters, $url_without_params);
        return $ret;
    }
    
    private static function _buildUrl($parameters, $url_without_params = null)
    {

        if (is_null($url_without_params))
        {
            $ret = self::getCurrentUrlWithoutParams();
        }
        else
        {
            $ret = $url_without_params;
        }
        
        if (isset($parameters) && !empty($parameters))
        {
            $ret .= '?';
            $first_parameter = true;
            foreach ($parameters as $param_key => $param_value) {
                if (!$first_parameter) $ret .= '&';
                $first_parameter = false;    
                if (isset($param_value) && !empty($param_value))
                {
                    $ret .= $param_key.'='.$param_value;
                }   
                else
                {
                    $ret .= $param_key;
                }
            }
        }
        
        return $ret;        
    }
 
}