<?php

namespace core\botplus\model;

use core\model\controller\model;

/**
 * Model for the Bot plus interface
 *
 * @author Dani Gilabert
 * 
 */
class botplus extends model
{
    protected $_properties = array(
        'code' => array('type' => 'string'),
        'epigraphs' => array('type' => 'array'),
        'messages' => array('type' => 'array')
    );
    
    protected $_id_COMPOSITION = array('type', 'code');
    
    
    public function __construct($id = null)
    {
        parent::__construct();
        $this->loadData($id);
    }
    
}