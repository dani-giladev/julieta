<?php

namespace core\ajax\controller;

use core\ajax\view\ajax as view;


/**
 * Send a JSON response to client according with the AJAX request
 *
 * @author Dani Gilabert
 */
class ajax extends view
{
       
    /**
    * The server says to client, that has received the request successfully
    * @param $msg : string . Optional message witch send it to the client
    * @return void . Make an echo to client
    */     
    public static function ohYeah($msg = '')
    {
        echo self::ohYeahView($msg);
    }
    
    /**
    * The server says to client, that has not received the request successfully or there have had a problem
    * @param $msg : string . Optional message witch send it to the client
    * @return void . Make an echo to client
    */      
    public static function fuckYou($msg = '')
    {
        echo self::fuckYouView($msg);
    }    
    
    /**
    * The server send data to client (ex: fill a store)
    * @param $object : object . Indexed object
    * @return void . Make an echo to client
    */     
    public static function sendData($object)
    {
        $arr = array();
    
        if (isset($object))
        {
            if(isset($object->rows))
            {
                $object = $object->rows;
            }
            foreach($object as $row)
            {
                if(isset($row->value))
                {
                    $row = $row->value;
                }       
                $arr[] = $row;
            }
        }
        
        echo self::sendDataView($arr);
    }   
    
    public static function send2Phone($data)
    {
        header('Access-Control-Allow-Origin: *');
        echo $data;           
    } 
}