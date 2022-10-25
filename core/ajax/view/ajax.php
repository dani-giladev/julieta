<?php

namespace core\ajax\view;


/**
 * AJAX response view
 *
 * @author Dani Gilabert
 */
class ajax
{
    
    public static function ohYeahView($data)
    {
        if (is_array($data))
        {
            return '{"success":true, "data":{"result":'.json_encode($data).'}}';
        }
        else
        {
            return '{"success":true, "data":{"result":"'.$data.'"}}';
        }
    }
    
    public static function fuckYouView($msg)
    {
        return '{"success":false, "data":{"result":"'.$msg.'"}}';
    }    
    
    public static function sendDataView($data)
    {
        $ret = '{"meta":{"code":1,"exception":[],"success":true,"message":null}, ';
        $ret .= '"data":{"total":'.count($data).', ';
        $ret .= '"results":'.json_encode($data).'}}';

        return $ret;
    } 
}