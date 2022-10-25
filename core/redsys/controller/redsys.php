<?php

namespace core\redsys\controller;

// Controllers
use core\config\controller\config;

// Libs
use lib\redsys\RedsysAPI;

/**
 * Redsys controller
 *
 * @author Dani Gilabert
 * 
 */
class redsys
{
    
    public function getNewApiHandler()
    {
        return new RedsysAPI();
    }
    
    public function isRedsysRequest($data)
    {
        return (isset($data->Ds_MerchantParameters));
    }
    
    public function getTotalPriceFormat($total_price)
    {
        return number_format(round($total_price, 2), 2, "", "");
    }
    
    public function isAuthorized($code)
    {
        if (($code >= 0 && $code <= 99))
        {
            return true;
        }
        
        switch ($code)
        {
            case 900:
            case 400:
                return true;
            default:
                return false;
        }
    }
    
    public function getMsg($code)
    {
        switch ($code)
        {
            case 101:
                return 'Tarjeta caducada';
            case 102:
                return 'Tarjeta en excepción transitoria o bajo sospecha de fraude';
            case 106:
                return 'Intentos de PIN excedidos';
            case 125:
                return 'Tarjeta no efectiva';
            case 129:
                return 'Código de seguridad (CVV2/CVC2) incorrecto';
            case 180:
                return 'Tarjeta ajena al servicio';
            case 184:
                return 'Error en la autenticación del titular';
            case 190:
                return 'Denegación del emisor sin especificar motivo';
            case 191:
                return 'Fecha de caducidad errónea';
            case 202:
                return 'Tarjeta en excepción transitoria o bajo sospecha de fraude con retirada de tarjeta';
            case 904:
                return 'Comercio no registrado en FUC';
            case 909:
                return 'Error de sistema';
            case 913:
                return 'Pedido repetido';
            case 944:
                return 'Sesión Incorrecta';
            case 950:
                return 'Operación de devolución no permitida';
            case 9912:
            case 912:
                return 'Emisor no disponible';
            case 9064:
                return 'Número de posiciones de la tarjeta incorrecto';
            case 9078:
                return 'Tipo de operación no permitida para esa tarjeta';
            case 9093:
                return 'Tarjeta no existente';
            case 9094:
                return 'Rechazo servidores internacionales';
            case 9104:
                return 'Comercio con “titular seguro” y titular sin clave de compra segura';
            case 9218:
                return 'El comercio no permite op. seguras por entrada /operaciones';
            case 9253:
                return 'Tarjeta no cumple el check-digit';
            case 9256:
                return 'El comercio no puede realizar preautorizaciones';
            case 9257:
                return 'Esta tarjeta no permite operativa de preautorizaciones';
            case 9261:
                return 'Operación detenida por superar el control de restricciones en la entrada al SIS';
            case 9913:
                return 'Error en la confirmación que el comercio envía al TPV Virtual (solo aplicable en la opción de sincronización SOAP)';
            case 9914:
                return 'Confirmación “KO” del comercio (solo aplicable en la opción de sincronización SOAP)';
            case 9915:
                return 'A petición del usuario se ha cancelado el pago';
            case 9928:
                return 'Anulación de autorización en diferido realizada por el SIS (proceso batch)';
            case 9929:
                return 'Anulación de autorización en diferido realizada por el comercio';
            case 9997:
                return 'Se está procesando otra transacción en SIS con la misma tarjeta';
            case 9998:
                return 'Operación en proceso de solicitud de datos de tarjeta';
            case 9999:
                return 'Operación que ha sido redirigida al emisor a autenticar';
            default:
                return 'Error indeterminado';
        }
    }
    
    public function getUrlKOResult($data)
    {
        $version = $data->Ds_SignatureVersion;
        $b64_MerchantParameters = $data->Ds_MerchantParameters;
	$ReceivedSignature = $data->Ds_Signature;
        
	$redsysAPI = $this->getNewApiHandler();
        
        // Check signature
        if (!$this->isSignatureOK($redsysAPI, $b64_MerchantParameters, $ReceivedSignature))
        {
            return array(
                'success' => false,
                'msg' => 'La firma del pago (signature) no és válida'
            );
        }
        
        // Get code
        $response_code = $this->getCode($redsysAPI);
        
        // Is authorized?
        if ($this->isAuthorized($response_code))
        {
            return array(
                'success' => false,
                'msg' => 'El pago se ha realizado pero ha ocurrido un problema en la confección del pedido. Por favor, pónganse en contacto con el comercio. Gracias.'
            );
        }
        
        // Happy end
        return array(
            'success' => true,
            'msg' => $this->getMsg($response_code)
        );
    }
    
    public function getUrlOKResult($data, $order_code)
    {
        $version = $data->Ds_SignatureVersion;
        $b64_MerchantParameters = $data->Ds_MerchantParameters;
	$ReceivedSignature = $data->Ds_Signature;
        
	$redsysAPI = $this->getNewApiHandler();
        
        // Check signature
        if (!$this->isSignatureOK($redsysAPI, $b64_MerchantParameters, $ReceivedSignature))
        {
            return array(
                'success' => false,
                'msg' => 'La firma del pago (signature) no és válida'
            );
        }
        
        // Get code
        $response_code = $this->getCode($redsysAPI);
        
        // Is authorized?
        if (!$this->isAuthorized($response_code))
        {
            return array(
                'success' => false,
                'msg' => $this->getMsg($response_code)
            );
        }
        
        // Check order code
        $ds_order = $redsysAPI->getParameter("Ds_Order");
        if ($order_code !== $ds_order)
        {
            return array(
                'success' => false,
                'msg' => 'El número de pedido del TPV virtual no corresponde con el del pedido en curso'
            );
        }
        
        // Happy end
        return array(
            'success' => true,
            'msg' => ''
        );
    }
    
    public function isSignatureOK($redsysAPI, $b64_MerchantParameters, $ReceivedSignature)
    {
        $is_development = config::getConfigParam(array("application", "development"))->value;
        $payment_params =  config::getConfigParam(array("application", "redsys"))->value;
        
        // Check signature
        $env = ($is_development)? 'development' : 'production';
        $kc = $payment_params->$env->secret_encryption_key; // Clave recuperada de CANALES
        $signature = $redsysAPI->createMerchantSignatureNotif($kc, $b64_MerchantParameters);
        return ($signature === $ReceivedSignature);
    }
    
    public function getCode($redsysAPI)
    {
        //$json_params = $redsysAPI->decodeMerchantParameters($b64_MerchantParameters);
        //$params = json_decode($json_params);
        $response_code = (int) $redsysAPI->getParameter("Ds_Response");
        return $response_code;    
    }
    
}