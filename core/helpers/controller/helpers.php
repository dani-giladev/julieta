<?php

namespace core\helpers\controller;

use core\config\controller\config as config;

/**
 * Bunch of global useful functions
 *
 * @author Dani Gilabert
 */
class helpers
{
    /**
     * Transforms json string to object
     *
     * @param $data: (string)
     * @return $ret (standard object).
     */
    public static function objectize($data)
    {
        $ret = (is_array($data)) ? json_decode(json_encode($data), false) : $data;
        
        return $ret;
    }
    
    /**
     * Return all the world countries translated according to the current language
     * http://www.countries-list.info/Download-List
     *
     * @param $lang: (string) the current language
     * @return array list of countries.
     *                  $countriesList = array(
     *                              ""   => "",
     *                              "AF" => "Afganistan",
     *                              "AL" => "Albània",
     *                              "DE" => "Alemanya",
     *                              etc..
     */          
    public static function getCountriesList($lang)
    {
        $countriesFile = 'res/countriesList/'.$lang.'.php';
        require($countriesFile);
        
        // Override some names:
        //  - Change Netherlands (Països baixos) by Holland
        if ($lang == 'ca' || $lang == 'es')
        {
            $countriesList['NL'] = 'Holanda';
        }
        else
        {
            $countriesList['NL'] = 'Holland';
        }            

        asort($countriesList);
        return $countriesList;
    }  
    
    /**
     * Get list of world languages
     *
     * @param $lang: (string) We want languages translated to this language
     * @return $languagesList (array).
     */
    public static function getLanguagesList($lang)
    {
        $languagesFile = 'res/languagesList/'.$lang.'.php';
        require($languagesFile);
        asort($languagesList);
        
        return $languagesList;
    }        
    
    /**
     * Get the number of days between two given dates
     *
     * @param $start_date: (string) Date in d-m-Y format
     * @param $end_date: (string) Date in d-m-Y format
     * @return $days_between.
     */
    public static function getNumberOfDays($start_date, $end_date)
    {
        $days = (strtotime($end_date) - strtotime($start_date)) / 86400;
        $days_between = \round(\abs($days));
        
        return  $days_between;
    }
    
    /**
     * Prepare internal command and execute it
     *
     * @param $script_path: (string) Script to be executed
     * @param $args: (array) Params to be passed to the script
     * @return $exitStatus.
     */ 
    public static function execScript($script_path, $args = array())
    {
        $output = array();
        $exitStatus = null;
        $params = '';
        if (!empty($args))
        {
            $first_time = true;
            foreach ($args as $value)
            {
                if (!$first_time)
                {
                    $params .= ' ';
                }
                $params .= $value;
                $first_time = false;
            }            
        }

        $script = "cd ".config::getConfigParam(array("application", "base_path"))->value." && ".
                  "php ".config::getConfigParam(array("application", "scripts_path"))->value."/".$script_path.".php ".$params." > /dev/null &";
        exec($script, $output, $exitStatus);
        
        return $exitStatus;
    }
    
    public static function removeSpecialChars($str)
    {
        $ret = preg_replace('/[^A-Za-z0-9\. -]/', '', $str);
        return $ret;
    }
    
    public static function normalizeSpecialChars($str)
    {
        $char_map = self::getBasicCharMap();
        $ret = str_replace(array_keys($char_map), $char_map, $str);
        return $ret;
    }
    
    public static function removeNoUtf8Chars($nonutf8)
    {
        $utf8 = mb_convert_encoding($nonutf8 , 'UTF-8', 'UTF-8');
        return $utf8;
    }
    
    public static function stripHtmlTags($html, $allowable_tags = '<b><i><u><br><ul><ol><li><p><em><strong><code><samp><kbd><var>')
    {
        $ret = $html;
        
        $ret = self::removeStringBetweenHtmlTag($ret, 'style');
        $ret = self::removeStringBetweenHtmlTag($ret, 'STYLE');
        $ret = strip_tags($ret, $allowable_tags);

        // Remove style attributes
        $ret = preg_replace( '/style=(["\'])[^\1]*?\1/i', '', $ret, -1 );
        
        $ret = nl2br($ret);
        $ret = str_replace(chr(10), "", $ret);
        $ret = str_replace(chr(13), "", $ret);
        return $ret;
    }
    
    public static function removeStringBetweenHtmlTag($string, $tag)
    {
        $search = '/<'.$tag.'[^>]*>([\s\S]*?)<\/'.$tag.'[^>]*>/';
        $replace = "";
        $ret = preg_replace($search, $replace, $string);
        return $ret;
    }
    
    public static function getBasicCharMap()
    {
        return array(
            // Latin
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C', 
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 
            'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O', 
            'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH', 
            'ß' => 'ss', 
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c', 
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 
            'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o', 
            'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th', 
            'ÿ' => 'y',
            // Latin symbols
            '©' => '(c)',
            // Greek
            'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
            'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
            'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
            'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
            'Ϋ' => 'Y',
            'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
            'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
            'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
            'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
            'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',
            // Turkish
            'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
            'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g', 
            // Russian
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
            'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
            'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
            'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
            'Я' => 'Ya',
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
            'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
            'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
            'я' => 'ya',
            // Ukrainian
            'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
            'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',
            // Czech
            'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U', 
            'Ž' => 'Z', 
            'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
            'ž' => 'z', 
            // Polish
            'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z', 
            'Ż' => 'Z', 
            'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
            'ż' => 'z',
            // Latvian
            'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N', 
            'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
            'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
            'š' => 's', 'ū' => 'u', 'ž' => 'z'
        );        
    }

    /**
     * Create a web friendly URL slug from a string.
     * 
     * Although supported, transliteration is discouraged because
     *     1) most web browsers support UTF-8 characters in URLs
     *     2) transliteration causes a loss of information
     *
     * @author Sean Murphy <sean@iamseanmurphy.com>
     * @copyright Copyright 2012 Sean Murphy. All rights reserved.
     * @license http://creativecommons.org/publicdomain/zero/1.0/
     *
     * @param string $str
     * @param array $options
     * @return string
     */
    public static function slugify($str, $options = array()) {
            // Make sure string is in UTF-8 and strip invalid UTF-8 characters
            $str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());

            $defaults = array(
                    'delimiter' => '-',
                    'limit' => null,
                    'lowercase' => true,
                    'replacements' => array(),
                    'transliterate' => true,
            );

            // Merge options
            $options = array_merge($defaults, $options);

            // Make custom replacements
            $str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);

            // Transliterate characters to ASCII
            $char_map = self::getBasicCharMap();
            if ($options['transliterate']) {
                    $str = str_replace(array_keys($char_map), $char_map, $str);
            }

            // Replace non-alphanumeric characters with our delimiter
            $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);

            // Remove duplicate delimiters
            $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);

            // Truncate slug to max. characters
            $str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');

            // Remove delimiter from ends
            $str = trim($str, $options['delimiter']);

            return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
    }   
    
    public static function sortArrayByField($array, $field, $sense = SORT_ASC)
    {
        $sort_field = array();
        
        if (count($array) <= 0)
        {
            return $array;
        }
        
        $array_is_object = (is_object($array));
        $items_are_objects = (is_object(reset($array)));
        $new_array = array();
        
        foreach($array as $key => $values)
        {
            if ($array_is_object || $items_are_objects)
            {
                $the_values = (array) $values;
            }
            else
            {
                $the_values = $values;
            }
            
            $sort_field[] = $the_values[$field];
            $new_array[$key] = $the_values;
        }

        array_multisort($sort_field, $sense, $new_array);

        if ($array_is_object || $items_are_objects)
        {
            if ($array_is_object && $items_are_objects)
            {
                $ret = new \stdClass();
                foreach ($new_array as $key => $values) {
                    $ret->$key = (object) $values;
                }                
            }
            elseif ($array_is_object && !$items_are_objects)
            {
                $ret = new \stdClass();
                foreach ($new_array as $key => $values) {
                    $ret->$key = $values;
                }                
            }
            elseif (!$array_is_object && $items_are_objects)
            {
                $ret = array();
                foreach ($new_array as $key => $values) {
                    $ret[$key] = (object) $values;
                }                
            }
        }
        else
        {
            $ret = $new_array;
        }
        
        return $ret;
    }
    
    /**
     * Return a sorted array by multiple fields
     *
     * @param $arrayToSort: the array to sort
     * @param $sortOrder: array which contains the fields to sort
     *              ex: array("field1" => "asc", "field2" => "desc", ...)
     * @return $arrayToSort already sorted
     */       
    public static function sortArrayBtMultipleFields($arrayToSort, $sortOrder) 
    { 
        $n_parameters = (count($sortOrder) * 2) + 1;
        $arg_list[0] = $arrayToSort; 
        $arg_list_counter=1;
        foreach($sortOrder as $sort_key => $sort_value)
        {
            $arg_list[$arg_list_counter] = $sort_key;
            $arg_list_counter++;
            $sense = ($sort_value == 'asc') ? SORT_ASC : SORT_DESC;
            $arg_list[$arg_list_counter] = $sense;
            $arg_list_counter++;
        }  

        $final_array = $arg_list[0]; 

        $toEval = "foreach (\$final_array as \$row){\n"; 
        for ($i=1; $i<$n_parameters; $i+=2) 
        { 
            $toEval .= "  \$field{$i}[] = \$row['$arg_list[$i]'];\n"; 
        } 
        $toEval .= "}\n"; 
        $toEval .= "array_multisort(\n"; 
        for ($i=1; $i<$n_parameters; $i+=2) 
        { 
            $toEval .= "  \$field{$i}, SORT_REGULAR, \$arg_list[".($i+1)."],\n"; 
        } 
        $toEval .= "  \$final_array);"; 
        eval($toEval);
        
        return $final_array; 
    }   
    
    public static function shuffleObject($object)
    {
        $arr = array();
        foreach ($object as $key => $values) {
            $values->tempkey = $key;
            $arr[] = $values;
        }
        
        shuffle($arr);
        
        $ret = array();
        foreach ($arr as $values) {
            $key = $values->tempkey;
            unset($values->tempkey);
            $ret[$key] = $values;
        }
        
        return self::objectize($ret);
    }
    
    public static function json2Html($jsonText = '')
    {
        $arr = json_decode($jsonText, true);
        $html = "";
        if ($arr && is_array($arr)) {
            $html .= self::_arrayToHtmlTableRecursive($arr);
        }
        return $html;
    }

    private static function _arrayToHtmlTableRecursive($arr) {
        $str = "<table><tbody>";
        foreach ($arr as $key => $val) {
            $str .= "<tr>";
            $str .= "<td>$key</td>";
            $str .= "<td>";
            if (is_array($val)) {
                if (!empty($val)) {
                    $str .= self::_arrayToHtmlTableRecursive($val);
                }
            } else {
                $str .= "<strong>$val</strong>";
            }
            $str .= "</td></tr>";
        }
        $str .= "</tbody></table>";

        return $str;
    }    
    
    /** 
     * Get substring between tags (strings)
     * 
     * Example:
     *      $fullstring = "this is my [tag]dog[/tag]";
     *      $parsed = getStringBetween($fullstring, "[tag]", "[/tag]");
     *      echo $parsed; // (result = dog)    
     */     
    public static function getStringBetween($string, $start, $end)
    {
        $string = " ".$string;
        $ini = strpos($string,$start);
        if ($ini == 0) return "";
        $ini += strlen($start);
        $len = strpos($string,$end,$ini) - $ini;
        return substr($string,$ini,$len);
    }
}