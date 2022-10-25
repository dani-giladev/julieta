<?php

namespace core\backend\controller;

// Controllers
use core\config\controller\config;
use core\ajax\controller\ajax;

/**
 *  Backend file manager controller
 *
 * @author Dani Gilabert
 * 
 */
class fileManager
{
    private $json = '';
    private $base_path = '';
    private $filemanager_path = '';
    
    public function __construct()
    {
        $this->module_id = 'core';
        $this->json .= '[';
        $this->base_path = config::getConfigParam(array("application", "base_path"))->value;
        $this->filemanager_path = config::getFilemanagerPath();
    }
    
    public function getDir($params)
    {
        $base_node = $params->base_node;
        
        $full_resources_path = $this->base_path . '/' . $this->filemanager_path;
        if (!empty($base_node))
        {
            $full_resources_path .= '/'.$base_node;
        }
        
        $this->_getSubDirs($full_resources_path, false);

        $this->json .= ']';
        $this->json = str_replace(",]", "]", $this->json);

        echo($this->json);
    }    
    
    private function _getSubDirs($dir, $child)
    {
        $root_dir = glob($dir . '/*' , GLOB_ONLYDIR);
        if ($root_dir === false)
        {
            return;
        }
        
        $full_resources_path = $this->base_path . '/' . $this->filemanager_path;
        
        foreach ($root_dir as $subdir)
        {
            $text = basename($subdir);
            $rel_path = str_replace($full_resources_path."/", "", $subdir);
            
            if (count(glob("$subdir/*")) === 0)
            {
                $this->json .= '{"text":"'.$text.'", "leaf": true, "id": "'.$rel_path.'"},';
            }
            else
            {
                $this->json .= '{"text":"'.$text.'", "id": "'.$rel_path.'", "children": [';
                $this->_getSubDirs($subdir, true);                
            }
        }            

        if ($child)
        {
            $this->json .=  ']},';
        }
    }
    
    public function getFiles($params)
    {
        $files = array();
        
        if (!isset( $params->base_node))
        {
            echo(json_encode(array('files' => $files)));
            return;
        }
        
        $base_node = $params->base_node;
        
        $full_resources_path = $this->base_path . '/' . $this->filemanager_path;
        
        if ($params->dir == 'root' || empty($params->dir))
        {
            $dir = $full_resources_path.'/'.$base_node;
        }
        else
        {
            $dir = $full_resources_path."/".$params->dir;
        }
        
        $objects = @scandir($dir);
        if ($objects !== false)
        {
            foreach ($objects as $file)
            {
                if ($file != '.' AND $file != '..' )
                {
                    $full_file_path = $dir . "/". $file;
                    if (filetype($full_file_path) == 'file')
                    {
                        $rel_path = str_replace($full_resources_path, "", $dir);  
                        if (strlen($rel_path) > 0)
                        {
                            if (substr($rel_path, 0, 1) === "/")
                            {
                                $rel_path = substr($rel_path, 1);
                            }
                        }
                        $files[] = array(
                            'filename' => $file,
                            'filesize' => round((filesize($full_file_path)/1024), 3). ' KB',
                            'filedate' => date("F d Y H:i:s", filemtime($full_file_path)),
                            'relativePath' => $rel_path
                        );
                    }
                }
            }
        }        

        echo(json_encode(array('files' => $files)));
    }
    
    public function newFolder($data)
    {
        $full_resources_path = $this->base_path . '/' . $this->filemanager_path;
        
        if(isset($data->base_node))
        {
            if (empty($data->base_node))
            {
                $base_node = $full_resources_path;
            }
            else
            {
                $base_node = $full_resources_path."/".$data->base_node;
            }
        }
        else
        {
            $base_node = $full_resources_path;
        }
        
        $target_dir = $base_node . '/'. $data->dir_name;
        
        if(@mkdir($target_dir))
        {
            ajax::ohYeah();
        }
        else
        {
            ajax::fuckYou();
        } 
    }
    
    public function deleteFolder($data)
    {
        if(!is_null($data->node))
        {
            if ($data->node == 'root')
            {
                ajax::fuckYou();
                return;
            }
            $full_resources_path = $this->base_path . '/' . $this->filemanager_path;
            if($this->_rrmdir($full_resources_path."/".$data->node))
            {
                ajax::ohYeah();
                return;
            }
            else
            {
                ajax::fuckYou();
                return;
            } 
        }
        else
        {
            ajax::fuckYou();
            return;
        }
    }
    
    private function _rrmdir($dir)
    {
        if (is_dir($dir))
        {
            $objects = @scandir($dir);
            if ($objects === false)
            {
                return false;
            }
            foreach ($objects as $object)
            {
                if ($object != "." && $object != "..")
                {
                    if (filetype($dir."/".$object) == "dir")
                    {
                        $ret = self::_rrmdir($dir."/".$object);
                        if ($ret === false)
                        {
                            return false;
                        }                        
                    }
                    else
                    {
                        $ret = @unlink($dir."/".$object);
                        if ($ret === false)
                        {
                            return false;
                        }
                    }
                }
            }
            reset($objects);
            $ret = @rmdir($dir);
            if ($ret === false)
            {
                return false;
            }
        }
        else
        {
            return false;
        }
        
        return true;
   }
    
    public function deleteFile($params)
    {
        $base_node = $params->base_node;
        $file = $params->file;
        
        $full_resources_path = $this->base_path . '/' . $this->filemanager_path;
        
        if(!is_null($file))
        {
            $file = str_replace('root', $base_node, $file); 
            $target_file = $full_resources_path."/".$file;
                    
            if (@unlink($target_file))
            {
                ajax::ohYeah();
            }
            else
            {
                ajax::fuckYou();
            } 
        }
    }
    
    public function showPDF($params)
    {
        $path = isset($params->path) ? $params->path : '';
        $path = str_replace('root/', '', $path); 
        $full_path = $this->base_path . '/' . $this->filemanager_path . '/' .$path;
        
        header('Content-type: application/pdf');
        header('Content-Disposition: inline; filename="'.$full_path.'"');
        @readfile($full_path);
    }
    
    public function downloadFile($params)
    {
        $path = isset($params->path) ? $params->path : '';
        $path = str_replace('root/', '', $path); 
        $full_path = $this->base_path . '/' . $this->filemanager_path . '/' .$path;
        
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($full_path).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($full_path));
        @readfile($full_path);
    }
    
    public function uploadFile($params)
    {
        $dir = $params->dir_id;
        //$max_file_size = $params->maxFileSize;
        
        // Check path
        if (!file_exists($dir))
        {
            ajax::fuckYou("Path doesn't exist: ".$dir);
            return;
        }
        
        if (!isset($_FILES) || empty($_FILES))
        {
            ajax::fuckYou("No files to upload!");
            return;
        }
        
        // reArrayFiles
        $file_post = $_FILES['files'];
        $file_ary = array();
        $file_count = count($file_post['name']);
        $file_keys = array_keys($file_post);
        for ($i=0; $i<$file_count; $i++) {
            foreach ($file_keys as $key) {
                $file_ary[$i][$key] = $file_post[$key][$i];
            }
        }
        
        foreach ($file_ary as $file_values)
        {
            $original_filename = $file_values['name'];
            $temp_filename = $file_values['tmp_name'];
            $error = $file_values['error'];
            $size = $file_values['size'] / 1024;
            if ($error != 0)
            {
                ajax::fuckYou($this->getUploadFileError($error));
                return;
            }
//            if ($size > $max_file_size)
//            {
//                ajax::fuckYou("Error uploading $original_filename. Excedeed MAX FILE SIZE");
//                return;
//            }

            // Set the path to upload
            $filename = $dir.'/'.$original_filename;

            $ret_move = move_uploaded_file ($temp_filename, $filename);     
        }
        
        ajax::ohYeah();
    }
    
    public function getUploadFileError($error_code)
    {
        switch ($error_code) {
            case 1:
                return 'El fichero subido excede la directiva upload_max_filesize de php.ini';
            case 2:
                return 'El fichero subido excede la directiva MAX_FILE_SIZE especificada en el formulario HTML';
            case 3:
                return 'El fichero fue sólo parcialmente subido';
            case 4:
                return 'No se subió ningún fichero';
            case 6:
                return 'Falta la carpeta temporal. Introducido en PHP 5.0.3.';
            case 7:
                return 'No se pudo escribir el fichero en el disco. Introducido en PHP 5.1.0.';
            case 8:
                return 'Una extensión de PHP detuvo la subida de ficheros. PHP no proporciona una forma de determinar la extensión que causó la parada de la subida de ficheros; el examen de la lista de extensiones cargadas con phpinfo() puede ayudar. Introducido en PHP 5.2.0.';
            default:
                return 'Error indeterminado';
        }
    }

}