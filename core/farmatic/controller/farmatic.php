<?php

namespace core\farmatic\controller;

// Models
use core\farmatic\model\farmatic as farmaticModel;

/**
 * Controller to handle the Farmatic interface
 *
 * @author Dani Gilabert
 * 
 */
class farmatic
{
    private $_model = null;
    
    public function __construct()
    {
        $this->_model = new farmaticModel();
    }
    
    public function getModel()
    {
        return $this->_model;
    }
    
    public function isConnected()
    {
        return ($this->_model->isConnected());
    }
    
    public function close()
    {
        $this->_model->close();
    }
    
    public function getArticleData($article_code)
    {     
        $ret = array();

        $data = $this->_model->getArticleData($article_code);
        
        $ret['code'] = $data->fields[0];
        $ret['description'] = iconv("CP1252", "UTF-8", $data->fields[1]);
        $ret['pvp'] = $data->fields[2];
        $ret['puc'] = $data->fields[3];
        $ret['pmc'] = $data->fields[4];
        $ret['stock'] = $data->fields[5];
        $ret['updateStock'] = ($data->fields[6] == 'S')? true : false;
        $ret['onLine'] = ($data->fields[7] == 1)? true : false;
        $ret['gtin'] = is_null($data->fields[8])? '' : str_pad(trim($data->fields[8]), 13, "0", STR_PAD_LEFT);
        
        $puc_margin = 0;
        if ($ret['puc'] > 0)
        {
            if ($ret['pvp'] > 0)
            {
                $puc_margin =  100 - (($ret['puc'] * 100) / $ret['pvp']);
            }            
        }
        else
        {
            $puc_margin = 100;
        }
        
        $ret['pucMargin'] = number_format(round($puc_margin, 2), 2, ".", "");;
        $ret['pmcMargin'] = 0;
        $ret['lastReading'] = date('d-m-Y H:i:s');        
        
        return $ret;
    }
    
    public function getStock($article_code)
    {      
        $ret = array();
        
        $data = $this->_model->getStock($article_code);
        
        $ret['stock'] = $data->fields[0];
        $ret['updateStock'] = ($data->fields[1] == 'S')? true : false;
        
        return $ret;
    }
    
    public function exist($article_code)
    {     
        $data = $this->_model->exist($article_code);
        $exist = ($data->fields[0] == 1);
        return $exist;
    }
    
    public function markOffAsOnlineOrOffline($article_code, $online)
    {
        $this->_model->markOffAsOnlineOrOffline($article_code, $online);
    }
    
    public function executeQuery($sql)
    {
        return $this->_model->executeQuery($sql);
    }
    
    public function getChangesInArticleCodes($date_time)
    {      
        $ret = array();
        
        $data = $this->_model->getChangesInArticleCodes($date_time);
        while(!$data->EOF)
        {
            $x = 0;
            $item = array();
            
            $item['old_code'] = iconv("CP1252", "UTF-8", $data->fields[$x]);$x++;
            $item['new_code'] = iconv("CP1252", "UTF-8", $data->fields[$x]);$x++;
            $item['date_time'] = iconv("CP1252", "UTF-8", $data->fields[$x]);$x++;

            $ret[$item['old_code']] = $item;
            $data->movenext();
        }
        
        return $ret;
    }
    
}