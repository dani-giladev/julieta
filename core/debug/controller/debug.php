<?php

namespace core\debug\controller;

use core\debug\view\debug as view;


/**
 * Debug controller
 *
 * @author Dani Gilabert
 * 
 */
class debug
{
    protected static $_start_time;
    protected static $_values = array();
    
    public static function setStartTime($value)
    {
        self::$_start_time = $value;
    }
    
    public static function setValue($values)
    {
        self::$_values[$values['key']] = $values;
    }   
    
    public static function render()
    {
        $view = new view();
        echo $view->render(self::$_start_time, self::$_values);
    }    
}