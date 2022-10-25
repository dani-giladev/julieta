<?php

namespace core\model\model;

use core\model\controller\model;

/**
 * Common basic model
 *
 * @author Dani Gilabert
 * 
 */
class basic extends model
{
    protected $_properties = array(
        'code' => array('type' => 'string'),
        'name' => array('type' => 'string'),
        'description' => array('type' => 'string'),
        'available' => array('type' => 'boolean')
    );
    
    protected $_id_COMPOSITION = array('type', 'code');
    
    
    public function __construct($id = null)
    {
        parent::__construct();
        $this->loadData($id);
    }
    
}