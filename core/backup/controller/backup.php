<?php

namespace core\backup\controller;

// Controllers
use core\config\controller\config;

// Models
use core\model\controller\model;

/**
 * Backup controller
 *
 * @author Dani Gilabert
 * 
 */
class backup
{
    private $_app_code = null;
    private $_app_name = null;
    private $_host = null;
    private $_fake = false;
    private $_no_confirmation = false;
    private $_no_tty = false;

    public function __construct($app_code, $app_name, $host)
    {
        $this->_app_code = $app_code;
        $this->_app_name = $app_name;
        $this->_host = $host;
    }    

    public function init()
    {
        $shortopts  = "";
        //$shortopts .= "x::";
        
        $longopts  = array(
            "help::",
            "noconfirmation::",
            "notty::",
            "fake::",
            "all::",
            "public::",
            "db::",
            "dbfull::",
            "dblocalsync::"
        );
        
        $options = getopt($shortopts, $longopts);
        // Test
//        $options["db"] = "db";
//        $options["noconfirmation"] = "noconfirmation";
        
        if (count($options) == 0)
        {
            $options["h"] = false;
        }
        
        foreach($options as $key => $value)
        {
            switch($key)
            {
                case "h":
                case "help":
                case "":
                    $this->_showHelp();
                    die();
                break;
                case "all":
                case "public":
                case "db":
                case "dbfull":
                case "dblocalsync":
                    
                break;
                case "fake":
                    $this->_fake = true;
                break;
                case "noconfirmation":
                    $this->_no_confirmation = true;
                break;
                case "notty":
                    $this->_no_tty = true;
                break;
                default:
                   echo "Sorry. Wrong param ".$key.".\n";
                   die();
                break;
            }
        }
        
        $this->_proceed($options);
        
    }
    
    protected function _proceed($options)
    {
        $do_all_backups = (key_exists("all", $options) || $this->_fake) ? true : false;
        $do_public_backup = (key_exists("public", $options) || $do_all_backups) ? true : false;
        $do_db_backup = (key_exists("db", $options) || $do_all_backups) ? true : false;
        $do_dbfull_backup = (key_exists("dbfull", $options) || $do_all_backups) ? true : false;
        $do_dblocalsync = (key_exists("dblocalsync", $options)) ? true : false;
        
        if (!$this->_no_confirmation)
        {
            $public_backup_msg = ($do_public_backup) ? "Public backup will be performed." : "";
            $db_backup_msg = ($do_db_backup) ? "Database backup will be performed." : "";
            $dbfull_backup_msg = ($do_dbfull_backup) ? "Database FULL backup will be performed." : "";
            $dblocalsync_msg = ($do_dblocalsync) ? "Local synchronization from dbbackup to <app db> will be performed." : "";

            $msg = <<<EOT
                Ok. Everything ready.
                {$public_backup_msg}
                {$db_backup_msg}
                {$dbfull_backup_msg}
                {$dblocalsync_msg}

                Type 'yes' to continue:
EOT;
            echo $msg;
            $handle = fopen ("php://stdin","r");
            $line = fgets($handle);
            if(trim($line) != 'yes')
            {
                echo "Aborted!".PHP_EOL.PHP_EOL;
                exit;
            }

            echo PHP_EOL;
            echo "Thank you, continuing...".PHP_EOL;            
        }
        echo PHP_EOL;
        
        if ($do_public_backup)
        {
            $this->_publicBackup();
        }
        
        if ($do_db_backup || $do_dbfull_backup || $do_dblocalsync)
        {
            $this->_dbBackup($do_db_backup, $do_dbfull_backup, $do_dblocalsync);
        }
        
        echo PHP_EOL."Process finished. Enjoy!".PHP_EOL.PHP_EOL;
    }
    
    private function _publicBackup()
    {
        echo 'Doing public backup... '.PHP_EOL;

        $path = '/var/www/html/'.$this->_app_code.'/backups/';

        $cmd = 
            'scp '.
                'root@'.$this->_host->ip.':'.$path.'lastbackup/'.$this->_app_code.'-public-lastbackup.zip'.
                ' '.
                $path.$this->_app_code.'-public-lastbackup-'.date('YmdHis').'.zip'.
            '';
        
        if (!$this->_no_tty)
        {
            $cmd .= ' > /dev/tty';
        }
        
        echo $cmd.PHP_EOL;
        if (!$this->_fake)
        {
            exec($cmd);
        }
        echo PHP_EOL;
    }
    
    private function _dbBackup($do_db_backup, $do_dbfull_backup, $do_dblocalsync)
    {
        echo 'Doing database backup... '.PHP_EOL;
        
        // Set target
        $rtarget_conn_params = config::getDBConnectionParams('main_database');
                
        // Firstly, setting tunnel
        if ($do_db_backup || $do_dbfull_backup)
        {
            echo 'Setting tunnel... '.PHP_EOL;
            $remote_cmd = 'ssh -f -L 5985:127.0.0.1:5984 root@'.$this->_host->ip.' sleep 60 > /dev/null 2>&1';
            echo 'Executing remote command: '.$remote_cmd.' on '.$rtarget_conn_params->host->value.PHP_EOL;
            if (!$this->_fake)
            {
                $user = 'administrador';
//                $pass = 'xxxxxxxx';
//                $connection = ssh2_connect($rtarget_conn_params->host->value);
//                $authentication = ssh2_auth_password($connection, $user, $pass); // $rtarget_conn_params->password->value);
                $connection = ssh2_connect($rtarget_conn_params->host->value, 22, array('hostkey' => 'ssh-rsa'));
                $authentication = ssh2_auth_pubkey_file($connection, $user,
                          '/home/administrador/.ssh/id_rsa.pub',
                          '/home/administrador/.ssh/id_rsa');
                if (!$authentication)
                {
                    echo 'Impossible setting tunnel to replicate database!!'.PHP_EOL;
                    return;
                }
                $stream = ssh2_exec($connection, $remote_cmd);            
            }

            // Set source
            $rsource_conn_params = unserialize(serialize($rtarget_conn_params));
            $rsource_conn_params->password->value = 'Finselscollons1';
            $rsource_conn_params->host->value = '127.0.0.1';
            $rsource_conn_params->port->value = '5985';
            $rsource_conn_params->dbname->value = $this->_app_code;          
        }

        // Set db model
        $db_model = new model();  

        // Replicate to existent database: <app code>-backup
        if ($do_db_backup)
        {
            $rtarget_conn_params->dbname->value = $this->_app_code.'-backup';
            echo 'Replicating: '.
                    $rtarget_conn_params->host->value.':'.$rtarget_conn_params->port->value.
                    ' ('.$rtarget_conn_params->dbname->value.')'.
                    ' < '.
                    $rsource_conn_params->host->value.':'.$rsource_conn_params->port->value.
                    ' ('.$rsource_conn_params->dbname->value.')'.
                    PHP_EOL;
            if (!$this->_fake)
            {
                $replication = $db_model->replicate($rsource_conn_params, $rtarget_conn_params);
                if ($replication !== true)
                {
                    echo "Error replication: ".$replication.PHP_EOL;
                }                
            }            
        }

        // Replicate to new database: <app code>-backup-YmdHmi
        if ($do_dbfull_backup)
        {
            $rtarget_conn_params->dbname->value = $this->_app_code.'-backup-'.date('YmdHmi');
            echo 'Replicating: '.
                    $rtarget_conn_params->host->value.':'.$rtarget_conn_params->port->value.
                    ' ('.$rtarget_conn_params->dbname->value.')'.
                    ' < '.
                    $rsource_conn_params->host->value.':'.$rsource_conn_params->port->value.
                    ' ('.$rsource_conn_params->dbname->value.')'.
                    PHP_EOL;
            if (!$this->_fake)
            {
                $replication = $db_model->replicate($rsource_conn_params, $rtarget_conn_params);
                if ($replication !== true)
                {
                    echo "Error replication: ".$replication.PHP_EOL;
                }   
            }            
        }

        // Local synchronization from dbbackup to <app db>
        if ($do_dblocalsync)
        {
            $rsource_conn_params = unserialize(serialize($rtarget_conn_params));
            $rsource_conn_params->dbname->value = $this->_app_code.'-backup';
            $rtarget_conn_params = unserialize(serialize($rsource_conn_params));
            $rtarget_conn_params->dbname->value = $this->_app_code;
            echo 'Replicating: '.
                    $rsource_conn_params->host->value.':'.$rsource_conn_params->port->value.
                    ' ('.$rsource_conn_params->dbname->value.')'.
                    ' > '.
                    $rtarget_conn_params->host->value.':'.$rtarget_conn_params->port->value.
                    ' ('.$rtarget_conn_params->dbname->value.')'.
                    PHP_EOL;
            if (!$this->_fake)
            {
                $replication = $db_model->replicate($rsource_conn_params, $rtarget_conn_params);
                if ($replication !== true)
                {
                    echo "Error replication: ".$replication.PHP_EOL;
                }                
            }            
        }
        
        echo PHP_EOL;
    }
    
    protected function _showHelp()
    {
        $help_text = <<<EOT
Usage:
./backup -h | --help        -> Show this help text
./backup --noconfirmation   -> no confirmation (no prompt)
./backup --notty            -> no tty output (for cron tasks)
./backup --all              -> will do all backups (databases and scripts)
./backup --public           -> will do public backup
./backup --db               -> will do database backup
./backup --dbfull           -> will do database FULL backup
./backup --dblocalsync      -> will do local synchronization from dbbackup to <app db>
./backup --fake             -> will simulate all process
                            
Examples:
./backup --all
./backup --public --db
./backup --public
./backup --db --dbfull --noconfirmation

\n\n
EOT;
        
        echo $help_text;
    }
    
}