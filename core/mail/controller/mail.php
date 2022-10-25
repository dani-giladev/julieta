<?php

namespace core\mail\controller;

// Controllers
use core\config\controller\config;
use core\log\controller\log;
use \PHPMailer\PHPMailer\PHPMailer;
use \PHPMailer\PHPMailer\Exception as mailerException;

/**
 * Send e-mail messages
 *
 * @author Dani Gilabert
 */
class mail
{    
    
    public static function send($subject, $body, $to, $params = array())
    {
        $ret = 0;
        $failures = array();
        $error_message = "";
        
        if (empty($params))
        {
            $params = json_decode(json_encode(config::getConfigParam(array("application", "mail"))->value), true);
        }
        
        $mailer = "smtp";
        $host = $params["host"];
        $port = $params["port"];
        $smtpauth = true;
        $secure = "tls"; //$params["secure"];
        $username = $params["username"];
        $password = $params["password"];
        $from = key($params["from"]);
        $from_name = $params["from"][$from];
        $ishtml = isset($params["ishtml"])? $params["ishtml"] : true;
                
        try
        {
            $phpmailer = new PHPMailer();
            if($mailer == "smtp")
            {
                $phpmailer->isSMTP();
            }
            $phpmailer->Mailer = $mailer;
            $phpmailer->From = $from;
            $phpmailer->FromName = $from_name;
            $phpmailer->Host = $host;
            $phpmailer->Port = $port;
            $phpmailer->SMTPAuth = $smtpauth;
            $phpmailer->SMTPSecure = $secure;
            $phpmailer->Username = $username;
            $phpmailer->Password = $password;

            $phpmailer->SMTPAutoTLS = false;
        
            // Set headers
            $phpmailer->addCustomHeader('List-Unsubscribe', '<mailto:it@deemm.com>, <https://www.deemm.com/?unsubscribe>');

            foreach($to as $email => $name)
            {
               $phpmailer->AddAddress($email, $name);
            }

            $phpmailer->CharSet = 'UTF-8';
            //$phpmailer->Encoding = 'quoted-printable';
            $phpmailer->Subject = $subject;
            //$phpmailer->Subject = "=?UTF-8?B?".base64_encode($subject)."?="; 
            $phpmailer->Body = $body;
            $phpmailer->IsHTML($ishtml);
            if ($ishtml)
            {
                $phpmailer->AltBody = $subject;
            }

            $ret = $phpmailer->Send();
            $error_message = $phpmailer->ErrorInfo;
        } 
        catch(mailerException $e)
        {
            $ret = false;
            $error_message .= json_encode($e);//$e->errorMessage();
        }
        catch (Exception $e)
        {
            $ret = false;
            $error_message .= json_encode($e);
        }
        
        $phpmailer->ClearAddresses();
        $phpmailer->ClearAttachments();
        
        return $ret;
    }
   
    public static function send_SwiftMailer($subject, $body, $to, $params = array())
    {
        $ret = 0;
        $failures = array();
        
        if (empty($params))
        {
            $params = json_decode(json_encode(config::getConfigParam(array("application", "mail"))->value), true);
        }
        
        $host = $params["host"];
        $port = (int) $params["port"];
        $secure = $params["secure"];
        $username = $params["username"];
        $password = $params["password"];
        $from = $params["from"];

        // Create the Transport
        $security = ($secure === "starttls")? "tls" : $secure;
        $transport = \Swift_SmtpTransport::newInstance($host, $port, $security)
          ->setUsername($username)
          ->setPassword($password)
        ;
        
        if ($secure === "starttls")
        {
            $transport->setStreamOptions(array('ssl' => array('allow_self_signed' => true, 'verify_peer' => false, 'verify_peer_name'  => false)));
        }
        
        // Create the Mailer using your created Transport
        $mailer = \Swift_Mailer::newInstance($transport);

        // Create a message
        $message = \Swift_Message::newInstance($subject);
        
        // Set headers
        $headers = $message->getHeaders();
        $headers->addTextHeader('List-Unsubscribe', '<mailto:it@deemm.com>, <https://www.deemm.com/?unsubscribe>');
        
        // Set message
        $message->setFrom($from);
        $message->setTo($to);
        $message->setBody($body, 'text/html');
        //$message->setContentType("text/html");
        $message->addPart($subject, 'text/plain');
        
        // Send the message  
        try
        {      
            $ret = $mailer->send($message, $failures);               
        }
        catch (\Swift_TransportException $STex) {
            // logging error
            $error = $STex->getMessage();
             log::setAppLog($error, log::ERROR);
        }
        catch (Exception $ex) {
            $error = $ex->getMessage();
            log::setAppLog($error, log::ERROR);
        }
      
        return ($ret === count($to));
    }
}