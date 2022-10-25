<?php

namespace core\debug\view;

// Controllers
use core\config\controller\config;

/**
 * Debug view
 *
 * @author Dani Gilabert
 * 
 */
class debug
{    
    
    public function render($start_time, $values)
    {       
        
        if (!config::getConfigParam(array("application", "development"))->value)
        {
            return '';
        }
        
        $html = PHP_EOL.PHP_EOL;
        $html .= '<div style="color:red; margin:20px; float:right;">';
        
        foreach ($values as $key => $value) {
            $html .= 
                '<div style="font-size:16px;">'.
                    $value['description'].' : '.$value['value'].
                '</div>';  
        }
        
        $total_time = number_format((microtime(true) - $start_time) * 1000, 2, ",", ".");
        $html .= '<div style="font-size:20px;">Total time of process: <b>'.$total_time.' ms</b></div>';
        
        $html .= '</div>'.PHP_EOL;
        
        return $html;
    } 
    
}