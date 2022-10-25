<?php

namespace core\botplus\controller;

// Controllers
use core\config\controller\config;
use core\helpers\controller\helpers;
use core\botplus\controller\webservice;

// Models
use modules\ecommerce\model\botplus as botplusModel;

/**
 * Botplus Controller
 *
 * @author Dani Gilabert
 * 
 */
class botplus extends webservice
{
    private $_botplus_path;    
    private $_epigraphs_path;
    private $_messages_path;
    private $_articles_path;
    private $_medicines_path;
    private $_parapharmacy_path;
    
    public function __construct()
    {
        parent::__construct();
        
        $base_path = config::getConfigParam(array("application", "base_path"))->value;
        $this->_botplus_path = $base_path.'/'.config::getBotplusPath();
        $this->_epigraphs_path = $this->_botplus_path.'/epigraphs';
        $this->_messages_path = $this->_botplus_path.'/messages';
        $this->_articles_path = $this->_botplus_path.'/articles';
        $this->_medicines_path = $this->_articles_path.'/medicamentos';
        $this->_parapharmacy_path = $this->_articles_path.'/parafarmacia';
    }

    public function getEpigraph($article)
    {
        $article_code = $article->code;
        $article_type = $article->articleType;

        if ($article_type == '1')
        {
            $idService = 'TEXTOSPARAFAR';
            $relpath = 'parafarmacia';
        }
        else
        {
            $idService = 'TEXTOS';
            $relpath = 'medicamentos';
        }            

        $filename = $this->_epigraphs_path.'/'.$relpath.'/'.$article_code;
        if (file_exists($filename))
        {
            return array(
                'success' => true,
                'msg' => "L'arxiu: $filename, ja existeix"
            );
        }

        //$raw_data = $this->BotPlus2($idService, $article_code);            
        $raw_data = $this->BotPlus2Key($idService, $article_code);
        if (!isset($raw_data['array']['Registro']))
        {
            $msg = $raw_data['array'][0];
            if ($msg === 'Respuesta vacia')
            {
                return array(
                    'success' => true,
                    'msg' => "No hi han textes per l'article: $article_code"
                );
            }
            return array(
                'success' => false,
                'msg' => "ERROR al obtenir dades: ".$article_code.' ('.$msg.')'
            );
        }
        $epigraphs = $raw_data['array']['Registro'];

        //$article_code = substr($epigraphs['ESPECOD'], 0, 6);
        $json_epigraphs = json_encode($epigraphs, JSON_PRETTY_PRINT);
        $output = file_put_contents($filename, $json_epigraphs);  

        return array(
            'success' => true,
            'msg' => ""
        );
    }

    public function getMessage($article)
    {
        $article_code = $article->code;
        $article_type = $article->articleType;
        
        $idService = 'MENSAJES';
            
        if ($article_type == '1')
        {
            $relpath = 'parafarmacia';
            return array(
                'success' => true,
                'msg' => "És un article de parafarmàcia"
            );
        }
        else
        {
            $relpath = 'medicamentos';
        }            
        
        $filename = $this->_messages_path.'/'.$article_code;
        if (file_exists($filename))
        {
            return array(
                'success' => true,
                'msg' => "L'arxiu: $filename, ja existeix"
            );
        }

        //$raw_data = $this->BotPlus2($idService, $article_code);            
        $raw_data = $this->BotPlus2Key($idService, $article_code);
        if (!isset($raw_data['array']['Registro']))
        {
            $msg = $raw_data['array'][0];
            if ($msg === 'Respuesta vacia')
            {
                return array(
                    'success' => true,
                    'msg' => "No hi han missatges per l'article: $article_code"
                );
            }
            return array(
                'success' => false,
                'msg' => "ERROR al obtenir dades: ".$article_code.' ('.$msg.')'
            );
        }
        $message = $raw_data['array']['Registro'];

        //$message_code = substr($message['ESPECOD'], 0, 6);
        $json_message = json_encode($message, JSON_PRETTY_PRINT);
        $output = file_put_contents($filename, $json_message);   

        return array(
            'success' => true,
            'msg' => ""
        );  
    }
    
    public function update($article)
    {
        $article_code = $article->code;
        $article_type = $article->articleType;
        
        if ($article_type == '1')
        {
            $relpath = 'parafarmacia';
        }
        else
        {
            $relpath = 'medicamentos';
        }

        $raw_epigraphs = array();
        $filename_epigraphs = $this->_botplus_path.'/epigraphs/'.$relpath.'/'.$article_code;
        if (file_exists($filename_epigraphs))
        {
            $raw_epigraphs = json_decode(file_get_contents($filename_epigraphs), true);
        }

        $raw_messages = array();
        if ($article_type == '2')
        {
            $filename_messages = $this->_botplus_path.'/messages/'.$article_code;
            if (file_exists($filename_messages))
            {
                $raw_messages = json_decode(file_get_contents($filename_messages), true);
            }
        }

        if (empty($raw_epigraphs) && empty($raw_messages))
        {
            return false;
        }

        $update_epigraphs = true;
        $update_messages = true;

        // Set model
        $id = 'ecommerce-botplus-'.$article_code;
        $botplus_model = new botplusModel($id);
        if ($botplus_model->exists())
        {
            $current_epigraphs = (array) $botplus_model->epigraphs;
            if (empty($raw_epigraphs) || !empty($current_epigraphs))
            {
                $update_epigraphs = false;
            }
            $current_messages = (array) $botplus_model->messages;
            if (empty($raw_messages) || !empty($current_messages))
            {
                $update_messages = false;
            }
        }
        else
        {
            $botplus_model->code = $article_code;
        }

        if (!$update_epigraphs && !$update_messages)
        {
            return false;
        }

        if ($update_epigraphs)
        {
            $epigraphs = array();
            if (isset($raw_epigraphs['ESPECOD']))
            {
                // Only one item
                $this->_addEpigraph($raw_epigraphs, $epigraphs);
            }
            else
            {
                foreach ($raw_epigraphs as $value) 
                {
                    $this->_addEpigraph($value, $epigraphs);
                }                   
            }             
            $botplus_model->epigraphs = $epigraphs;
        }

        if ($update_messages)
        {
            $messages = array();
            if (isset($raw_messages['CODTIPOMENSAJE']))
            {
                // Only one item
                $this->_addMessage($raw_messages, $messages);
            }
            else
            {
                foreach ($raw_messages as $value) 
                {
                    $this->_addMessage($value, $messages);
                }                   
            }             
            $botplus_model->messages = $messages;
        }

        // Save
        $botplus_model->save();   
            
        return true;
    }           
    
    private function _addEpigraph($value, &$array)
    {
        if (!isset($value['DES']) || !isset($value['TEXTO']))
        {
            return;
        }
        $name = $value['DES'];
        $raw_text = $value['TEXTO'];
        
        $this->_add($name, $raw_text, $array);
    }
    
    private function _addMessage($value, &$array)
    {
        if (!isset($value['TIPOMENSAJE']) || !isset($value['MENSAJE']))
        {
            return;
        }
        $name = $value['TIPOMENSAJE'];
        $raw_text = $value['MENSAJE'];
        
        $this->_add($name, $raw_text, $array);
    }
    
    private function _add($name, $raw_text, &$array)
    {
        if (empty($name) || empty($raw_text))
        {
            return;
        }

        $text = helpers::stripHtmlTags($raw_text);
        //$text = html_entity_decode($text);

        if (isset($array[$name]))
        {
            $array[$name]['es']['text'] .= '<br><br>'.$text;
        }
        else
        {
            $array[$name] = array(
                'enabled' => true,
                'es' => array(
                    'name' => $name,
                    'text' => $text
                )                    
            );                        
        }        
    }
    
    public function remove($article)
    {
        $article_code = $article->code;

        // Set model
        $id = 'ecommerce-botplus-'.$article_code;
        $model = new botplusModel($id);
        if (!$model->exists())
        {
            return false;
        }

        // Delete
        $model->delete();
        
        return true;
    }
    
    public function exist($article_code, $is_medicine = true)
    {
        $path = $is_medicine? $this->_medicines_path: $this->_parapharmacy_path;
        $filename_path = $path.'/'.$article_code;
        return (file_exists($filename_path));
    }  
    
    public function isMSR($article_code, $maindata = null)
    {
        $ret = false;
        
        if (!isset($maindata))
        {
            $maindata = $this->getMaindata($article_code);
        }
        
        if (isset($maindata) && isset($maindata['DATOSFARMACEUTICOS']['Registro']) && !empty($maindata['DATOSFARMACEUTICOS']['Registro']))
        {
            foreach ($maindata['DATOSFARMACEUTICOS']['Registro'] as $values)
            {
                if (isset($values['CODVALORATO']) && is_string($values['CODVALORATO']) && $values['CODVALORATO'] === 'MSR')
                {
                    return true;
                }
            }
        } 
            
        return $ret;
    }   
    
    public function getMaindata($article_code, $is_medicine = true)
    {
        if (!$this->exist($article_code, $is_medicine))
        {
            return null;
        }
        
        $path = $is_medicine? $this->_medicines_path: $this->_parapharmacy_path;
        $filename_path = $path.'/'.$article_code;
        
        return json_decode(file_get_contents($filename_path), true);
    }
    
    public function isAuthorized($article_code, $article_type, $maindata = null)
    {
        if (!isset($maindata))
        {
            $maindata = $this->getMaindata($article_code);
        }
        
        if (!isset($maindata))
        {
            return true;
        }
        
        if (isset($maindata['COD_ESTADO'])) //$article_type == '1')
        {
            // Parafarmàcia
            $status = $maindata['COD_ESTADO'];
        }
        else
        {
            // Medicaments
            if (!isset($maindata['CODESTADO']))
            {
                return false;
            }
            $status = $maindata['CODESTADO'];
        }
        
        return ($status === '0');
    }
    
    public function getStatusName($article_code, $maindata = null)
    {
        if (!isset($maindata))
        {
            $maindata = $this->getMaindata($article_code);
        }
        
        if (!isset($maindata))
        {
            return '';
        }

        return $maindata['DESCRIPCION'];
    }
    
}