<?php

namespace core\model\controller;

/**
 * Static db class
 *
 * @author Dani Gilabert
 * 
 */
class db
{    
    private static $_dbs = array();
        
    public static function getDb($db_id)
    {
        if (!array_key_exists($db_id, self::$_dbs))
        {
            return null;
        }
        
        return self::$_dbs[$db_id];
    }
        
    public static function setDb($db_id, $db)
    {
        self::$_dbs[$db_id] = $db;
    }
   
}