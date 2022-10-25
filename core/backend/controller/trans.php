<?php

namespace core\backend\controller;

use core\log\controller\log;
use core\backend\controller\lang;
use core\helpers\controller\helpers;

/**
 * Handle global translations behaviour
 *
 * @author Dani Gilabert
 */
class trans extends lang
{
    const CLIENT = "client";
    const SERVER = "server";
    
    /**
    * Folder path where will be created all the json files, and they contain all
    * the translations in the selected language
    * @access private
    * @var string . Translations folder path
    */   
    private $_translations_folder_path = '';

    /**
    * Translations doc path (the file has xls/ods format)
    * @access private
    * @var string . Translations xls file path
    */      
    private $_translations_xlsfile_path = '';
    
    /**
    * File witch contains the current version of the translations document
    * @access private
    * @var string . Translations current version file path
    */      
    private $_translations_current_version_file_path = '';
        
    /**
    * Constructor. Initialization properties
    * @param $module_id: string . Module name to build the resources path
    */      
    public function __construct($module_id)
    {
        if (strpos($module_id, "\\") !== false)
        {
            $module_id_pieces = explode("\\", $module_id);
            $module_id = $module_id_pieces[count($module_id_pieces)-1];
        }
        if ($module_id === 'core')
        {
            $this->_translations_folder_path = 'core/backend/res/lang';
        }
        else
        {
            $this->_translations_folder_path = 'modules/'.$module_id.'/backend/res/lang';
        }
        $this->_translations_xlsfile_path = $this->_translations_folder_path.'/translations.ods';
        $this->_translations_current_version_file_path = $this->_translations_folder_path.'/version.json';
    }    
       
    /**
    * Get the module translations content of the client or server, according with the current language
    * @param $whom : constant . The method will return the client or server translations depends on this param
    * @return object . Return the client or server translations of the specified module
    */     
    public function getTranslations($whom = self::CLIENT)
    {
        $ret = array();
        
        $lang = self::getLanguage();
        $path_file = $this->_translations_folder_path.'/'.$whom.'/'.$lang.'.json';
        if(!file_exists($path_file))
        {
            $msg = "Translations file doesn't exist in ".$path_file.".";
//                log::setAppLog($msg, log::WARNING);
            throw new \Exception($msg);                
            return false;
        }
        $object = json_decode(file_get_contents($path_file));
        
        if ($whom == self::CLIENT)
        {
            $item = array();
            foreach ($object as $key => $value) {
                $item['id'] = $key;
                $item['trans'] = $value;
                $ret[] = $item;
            }           
            $ret = helpers::objectize($ret);
        }
        else
        {
            $ret = $object;
        }

        return $ret;
    }    
       
    /**
    * Create client and server translation files from the translation xls file.
    * It will create 2 json files with the client and server translations respectively
    * @return boolean . Return true if the translations have been successful
    */ 
    public function createTranslationsFiles()
    {       
        $translations_xlsfile_path = $this->_translations_xlsfile_path;
        $translations_current_version_file_path = $this->_translations_current_version_file_path;
        
        // Check for the version control file 
        if(!file_exists($translations_current_version_file_path))
        {
            $msg = "Translations current version file doesn't exist in ".$translations_current_version_file_path.".";
//                log::setAppLog($msg, log::WARNING);
            throw new \Exception($msg);                
            return false;
        }           
        
//        // Giving permissions to www-data
//        chmod($translations_current_version_file_path, 0774);        
//        chown($translations_current_version_file_path, 'www-data');
        
        // Get data object
        $json = file_get_contents($translations_current_version_file_path);
        $translations_current_version_object = json_decode($json);

        // Checks for the xls file 
        if(!file_exists($translations_xlsfile_path))
        {
            $msg = "Translations xls/ods file doesn't exist in ".$translations_xlsfile_path.".";
//                log::setAppLog($msg, log::WARNING);
            throw new \Exception($msg);                
            return false;
        }
        // Get data xls object
        $objPHPExcel = $this->_getTranslationsDocument($translations_xlsfile_path);
        if (!isset($objPHPExcel))
        {
            return false;
        }

        // Check if the version number of the xls document is higher than the version control file,
        // then, we must create the new translations files
        $control_version = $translations_current_version_object->current_version;
        $xls_version = $this->_getVersionFromTranslationsDocument($objPHPExcel);            
        if (version_compare($control_version, $xls_version, ">=")) 
        { 
            return false;
        }  

        // Get data from translations document
        $supported_langs = (array) self::getSupportedCodeLanguages();
        $data = $this->_getDataFromTranslationsDocument($objPHPExcel, $supported_langs);
        
        // Create client translation files
        $this->_createTranslationsFilesForEachLanguage($supported_langs, 'client', $data);
        // Create server translation files
        $this->_createTranslationsFilesForEachLanguage($supported_langs, 'server', $data);        
        
        // Update the version control file with the current version        
        $translations_current_version_object->last_update_version = $translations_current_version_object->current_version;
        $translations_current_version_object->current_version = $xls_version;
        $translations_current_version_object->last_update_date = date("d-m-Y H:i:s");
        $json = json_encode($translations_current_version_object);
        file_put_contents($translations_current_version_file_path, $json);
    }
       
    /**
    * Get the xls translation file
    * @param $file_path : string . xls translation file path
    * @return object . Return a xls object
    */ 
    private function _getTranslationsDocument($file_path)
    {
        $ret = null;
        
        $objReader = \PHPExcel_IOFactory::createReader('OOCalc');
        try
        {
            $ret = $objReader->load($file_path);
        }
        catch(Exception $e)
        {
            $msg = 'ERROR while opening '.$file_path." document: ". $e;
            log::setAppLog($msg, log::ERROR);
        }        
        return $ret;
    }
       
    /**
    * Get the version of the xls translation file. It's on the first sheet
    * @param $objPHPExcel : object . The xls object
    * @return object . Return the xls document version
    */ 
    private function _getVersionFromTranslationsDocument($objPHPExcel)
    {
        $data_sheet = $objPHPExcel->getSheet(0); 
        $ret = $data_sheet->getCell('A1')->getValue();
        return $ret;
    }
       
    /**
    * Get the formatted data arranged to be treated in order to create the json translation file
    * @param $objPHPExcel : object . The xls object
    * @param $supported_langs : array . Application supported languages
    * @return array . Return an array with the module translations
    */
    private function _getDataFromTranslationsDocument($objPHPExcel, $supported_langs)
    {
        $ret = array();
        $lang_headers = array();
        $firstColumnLang = 4;
        
        // Get data sheet
        $data_sheet = $objPHPExcel->getSheet(1); 
        
        //  Get worksheet dimensions
        $highestRow = $data_sheet->getHighestRow(); 
        $highestColumn = $data_sheet->getHighestColumn();

        //  Loop through each row of the worksheet in turn
        for ($row = 1; $row <= $highestRow; $row++){ 
            //  Read a row of data into an array
            $rangeToArray = $data_sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);
            $dataRow = $rangeToArray[0];
            if ($row == 1)
            {
                // First row (check if the language is supported)
                for ($i = ($firstColumnLang-1); $i < count($dataRow); $i++) {
                    $lang = array();
                    $lang['available'] = (in_array($dataRow[$i], $supported_langs))? true : false;
                    $lang['lang'] = $dataRow[$i];
                    $lang_headers[] = $lang;
                }
            }
            else
            {
                $client = ($dataRow[0] == 'yes')? true : false;
                $server = ($dataRow[1] == 'yes')? true : false;
                $id = $dataRow[2];
                for ($i = ($firstColumnLang-1); $i < count($dataRow); $i++) {
                    if ($lang_headers[$i-($firstColumnLang-1)]['available'])
                    {
                        $lang = $lang_headers[$i-($firstColumnLang-1)]['lang'];
                        if ($client)
                        {
                            $ret['client'][$lang][$id] = $dataRow[$i];
                        }
                        if ($server)
                        {
                            $ret['server'][$lang][$id] = $dataRow[$i];
                        }                        
                    }
                }              
            }
        }  
        
        return $ret;
    }
       
    /**
    * Create the client or server translations files for each supported language
    * @param $supported_langs : array . Application supported languages
    * @param $type : string . client or server
    * @param $data : array . Array with the module translations
    */
    private function _createTranslationsFilesForEachLanguage($supported_langs, $type, $data)
    {
        // Create translations files (one for each lang)        
        $destination_folder = $this->_translations_folder_path;
        
        foreach ($supported_langs as $lang)
        {           
            $json = json_encode($data[$type][$lang]);
            $file_path = $destination_folder.'/'.$type.'/'.$lang.'.json';
            if (file_exists($file_path)) {
               // remove
               unlink($file_path);
            }
            // create file
            $file_handle = fopen($file_path, 'w');
            fwrite($file_handle, $json);
            fclose($file_handle);            
        }        
    }
    
}
