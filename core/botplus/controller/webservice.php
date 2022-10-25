<?php

namespace core\botplus\controller;

/**
 * Controller to handle the Botplus interface
 *
 * @author Dani Gilabert
 * 
 */
class webservice
{
    //private $_url = "http://swbdm.portalfarma.com/WebServiceBDM.asmx";
    private $_url = "http://actualizacion.portalfarma.com/Actualizador/WebServiceBDM.asmx";
    private $_idVerifUser = "E55077127";
    //private $_idVerifUser = "USU0437318";
    private $_username = "IDPONSGASCON";
    //private $_key = "U1NQbHVsenpqZkFt";
    private $_key = "U1NQbHVjemt6dUFt"; // 2018-03-23
    
    public function __construct()
    {
        
    }
    
    public function BotPlus2($idService, $parametros, $idVerifUser = null, $username = null, $idService_is_cypher = false)
    {
        $ws = "BotPlus2";
        $url = $this->_url."/".$ws;
        
        // idVerifUser
        if (is_null($idVerifUser))
        {
            $idVerifUser = base64_encode($this->_idVerifUser);
        }
        
        // User
        if (is_null($username))
        {
            $username = $this->_cipher($this->_username, $idVerifUser);
        }
        
        if (!$idService_is_cypher)
        {
            $idService = $this->_cipher($idService, $idVerifUser);
        }
        
        $post_params = array(
            'idVerifUser' => $idVerifUser,
            'user' => $username,
            'idservice' => $idService,
            'parametros' => $parametros
        );
        
        return $this->_getData($url, $post_params);
    }
    
    public function BotPlus2Key($idService, $parametros)
    {
        $ws = "BotPlus2Key";
        $url = $this->_url."/".$ws;
        
        $idVerifUser = base64_encode($this->_idVerifUser);
        $key = $this->_cipher($this->_key, $idVerifUser);
        $username = $this->_cipher($this->_username, $idVerifUser);
        $idService = $this->_cipher($idService, $idVerifUser);
        
        $post_params = array(
            'idVerifUser' => $idVerifUser,
            'key' => $key,
            'username' => $username,
            'idservice' => $idService,
            'parametros' => $parametros
        );
        
        return $this->_getData($url, $post_params);
    }
    
    public function ActivaKeyEI()
    {
        $ws = "ActivaKeyEI";
        $url = $this->_url."/".$ws;
        
        $idVerifUser = base64_encode($this->_idVerifUser);
        $key = $this->_cipher($this->_key, $idVerifUser);
        $username = $this->_cipher($this->_username, $idVerifUser);
        
        $post_params = array(
            'idVerifUser' => $idVerifUser,
            'key' => $key,
            'username' => $username
        );
        
        return $this->_getData($url, $post_params);
    }
    
    private function _getData($url, $post_params)
    {
        $post_fields = http_build_query($post_params);
        
        // abrimos la sesión cURL
        $ch = curl_init();

        // definimos la URL a la que hacemos la petición
        curl_setopt($ch, CURLOPT_URL, $url);
        // indicamos el tipo de petición: POST
        curl_setopt($ch, CURLOPT_POST, TRUE);
        // definimos cada uno de los parámetros
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

        // recibimos la respuesta y la guardamos en una variable
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $remote_server_output = curl_exec ($ch);
        
        // cerramos la sesión cURL
        curl_close ($ch);
 
        //libxml_use_internal_errors(true);
        $remote_server_output = str_replace("Para&amp;#x1;", "", $remote_server_output);
        file_put_contents('/tmp/file1', $remote_server_output);
        $xml = html_entity_decode($remote_server_output);
        file_put_contents('/tmp/file2', $xml);
        $object = simplexml_load_string($xml);
        $array = json_decode( json_encode($object) , 1);
        //$errors = libxml_get_errors();
            
        return array(
            'xml' => $xml,
            'object' => $object,
            'array' => $array
        );
    } 
    
    private function _cipher($texto, $clave)
    {
        // Fix key (dani)
        $clave = str_replace("=", "", $clave);
        
        // Tabla de cifrado
        $tablaCifrado = array(
            'q', 'w', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p', 
            'a', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 
            'z', 'x', 'c', 'v', 'b', 'n', 'm', 
            'Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P', 
            'A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 
            'Z', 'X', 'C', 'V', 'B', 'N', 'M', 
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '-', '_');
        
        // Hacemos la clave de una longitud mayor o igual al texto a cifrar
        while (strlen($clave) < strlen($texto))
        {
            $clave .= $clave;
        }
        
        // Creamos los arrays necesarios
        $listaTexto = array();
        $listaClave = array();
        $listaCoded = array();
        $coded = "";
        
        for ($i=0; $i< strlen($texto); $i++)
        {
            $t = $texto[$i];
            $listaTexto[$i] = array_search($t, $tablaCifrado);
            $c = $clave[$i];
            $listaClave[$i] = array_search($c, $tablaCifrado);
            $listaCoded[$i] = $this->_mod(($listaTexto[$i] + $listaClave[$i]), 64);
            $coded .= $tablaCifrado[$listaCoded[$i]];
            
            if ($coded === '4u2HH-kGIwV')
            {
                $test = true;
            }
        }
        
        return $coded;
    }
    
    private function _mod($x, $m)
    {
        $ret = ($x % $m + $m) % $m;
        return $ret;
    }
    
    
}