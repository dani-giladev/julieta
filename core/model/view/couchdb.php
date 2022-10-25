<?php

namespace core\model\view;

/**
 * Couchdb view
 *
 * @author Dani Gilabert
 * 
 */
class couchdb implements \Doctrine\CouchDB\View\DesignDocument
{
    
    protected $_map = array();
    
    public function __construct($map = null)
    {
        $this->_map = json_decode($map, true);
    }

    public function getData()
    {
        $views = $this->_map['views'];
        
        return array(
            'language' => 'javascript',
//            'views' => array(
//                'by_author' => array(
//                    'map' => 'function(doc) {
//                        if(\'article\' == doc.type) {
//                            emit(doc.author, doc._id);
//                        }
//                    }',
//                    'reduce' => '_count'
//                ),
//            ),
            'views' => $views
        );
    }  
    
}