<?php

namespace core\deploy\controller;

/**
 * Deploy controller
 *
 * @author Dani Gilabert
 * 
 */
class deploy
{
    private $_app_code = null;
    private $_app_name = null;
    private $_host = null;
    private $_version_file = null;
    private $_old_version_value = null;
    private $_new_version_value = null;
    private $_fake = false;
    private $_no_confirmation = false;
    private $_builder_file = "build.sh";

    public function __construct($app_code, $app_name, $host)
    {
        $this->_app_code = $app_code;
        $this->_app_name = $app_name;
        $this->_host = $host;
        $this->_version_file = "/home/administrador/NetBeansProjects/".$this->_app_code."/version";
    }    

    public function init()
    {
        $shortopts  = "";
        $shortopts .= "f::";
        $shortopts .= "v::";
        $shortopts .= "h::";
        $shortopts .= "r::";
        $shortopts .= "d::";
        $shortopts .= "c::";
        
        $longopts  = array(
            "help::",
            "fake::",
            "noconfirmation::"
        );
        
        $options = getopt($shortopts, $longopts);
        
        if(count($options) == 0)
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
                case "v":
                    $this->_updateVersionFile($value);
                break;
                case "f":
                   $this->_updateVersionFile(null, $value);
                break;
                case "r":
                case "d":
                case "c":
                    
                break;
                case "fake":
                    $this->_fake = true;
                break;
                case "noconfirmation":
                    $this->_no_confirmation = true;
                break;
                default:
                   echo "Sorry. Wrong param ".$key.".\n";
                   die();
                break;
            }
        }
        
        $doBuild = (key_exists("v", $options) || key_exists("f", $options)) ? true : false;
        $doPush = (key_exists("r", $options)) ? true : false;
        $doDeploy = (key_exists("d", $options)) ? true : false;
        $doCompact = (key_exists("c", $options)) ? true : false;
        
        return $this->_proceed($doBuild, $doPush, $doDeploy, $doCompact);
    }
    
    protected function _proceed($doBuild = true, $doPush = false, $doDeploy = false, $doCompact = false)
    {
        
        $fake_msg = $this->_fake? " (In fake mode)" : "";
        
        $msg = <<<EOT
                ******************************************
            
                Deploying... {$this->_app_name}{$fake_msg}
                
                ******************************************

EOT;
        echo $msg;
        
        if (!$this->_no_confirmation)
        {
            $build_msg = ($doBuild) ? ("Software version will be updated from ".$this->_old_version_value." to ".$this->_new_version_value) : "";
            $push_msg = ($doPush) ? "Files will be sent to repositories." : "";
            $deploy_msg = ($doDeploy) ? "Changes will be deployed to production hosts." : "";
            $compact_msg = ($doCompact) ? ("UI will be compacted.") : "";
            
            $msg = <<<EOT
                Ok. Everything ready.
                IMPORTANT! You must execute this script from DEVELOPMENT BRANCH.
                
                {$build_msg}
                
                {$compact_msg}

                {$push_msg}

                {$deploy_msg}

                Type 'yes' to continue:
EOT;
            echo $msg;
            $handle = fopen ("php://stdin","r");
            $line = fgets($handle);
            if(trim($line) != 'yes')
            {
                echo "ABORTING!\n";
                exit;
            }

            echo "\n";
            echo "Thank you, continuing...\n";            
        }
        echo PHP_EOL;
        
        if ($doBuild)
        {
            if (!$this->_fake)
            {
                file_put_contents($this->_version_file, $this->_new_version_value);
                $this->_createUILinks();                
            }
            else
            {
                echo "Simulated version upgrade from ".$this->_old_version_value." to ".
                        $this->_new_version_value.PHP_EOL;
                $this->_createUILinks();
            }
        }
        
        if($doCompact)
        {
            $this->_compactUI();
        }
        
        if($doPush)
        {
            $this->_gitPush();
        }
        
        if($doDeploy)
        {
            $this->_deploy();
        }
        
        echo "\n\nProcess finished. Enjoy!\n\n";
        
        return true;
    }
    
    protected function _createUILinks()
    {
        $target_path = "UI/app/plugins/";

        foreach (new \DirectoryIterator('modules') as $file_info) 
        {
            if($file_info->isDot()) continue;

            if($file_info->isDir())
            {
                if($file_info->isDir())
                {
                    $t_mod_path = $target_path.$file_info->getPathname()."/backend";
                    $target_mod_path = $t_mod_path."/UI";
                    
                    if(!\file_exists($t_mod_path))
                    {
                        @\mkdir($t_mod_path, 0755, true);
                    }

                    @\symlink("../../../../../../".$file_info->getPathname()."/backend/UI", $target_mod_path);
                }
            }
        }         
    }
    
    protected function _compactUI()
    {
        $cmd = '';
        $cmd .= "./".$this->_builder_file.' '.$this->_new_version_value;
        
        if (!$this->_fake)
        {
            system($cmd);
        }
        else
        {
            echo $cmd.PHP_EOL;
        }
    }
    
    protected function _gitPush()
    {
        $git_orders = array(
            'git add .',
            'git commit -m "'.$this->_app_name.' version '.rtrim(file_get_contents($this->_version_file)).' ready to publish."',
            'git pull origin development',
            'git push origin development',
            'git checkout production',
            'git merge development',
            'git pull origin production',
            'git push origin production',
            'git checkout development',
            
            'cd ../julieta',
            'git add .',
            'git commit -m "'.$this->_app_name.' version '.rtrim(file_get_contents($this->_version_file)).' ready to publish."',
            'git pull origin development',
            'git push origin development',
            'git checkout production',
            'git merge development',
            'git pull origin production',
            'git push origin production',
            'git checkout development',
            
            'cd ../maryann'
        );
        
        //$command = 'eval `keychain --eval '.$this->_app_code.'_rsa` \'';
        $command = 'eval `keychain --eval id_rsa` \'';
        foreach($git_orders as $order)
        {
            $command .= 'echo \'\'; echo $ '.$order.'; echo \'\';';
            if ($this->_fake)
            {
                continue;
            }
            $command .= $order.';';
        }
        $command .= '\'';
        
        system($command);
    }
    
    protected function _deploy()
    {
        $ip = $this->_host->ip;
        $name = $this->_host->name;
        
        if (!$this->_fake)
        {
            echo 'Deploying... '.$name.PHP_EOL;
            system('ssh root@'.$ip.' bash -s < deploy-on-server');
        }
        else
        {
            echo 'ssh root@'.$ip.' bash -s < deploy-on-server'.PHP_EOL;
        }
    }
    
    protected function _updateVersionFile($version_segment, $forced_version = null)
    {
        $this->_old_version_value = rtrim(file_get_contents($this->_version_file));
        
        if(!is_null($forced_version))
        {
            $this->_new_version_value = $forced_version;
        }
        else
        {
            $c_version = preg_split("/\./", $this->_old_version_value);
            
            switch($version_segment)
            {
                case "major":
                    $c_version[0]++;
                    $c_version[1] = 0;
                    $c_version[2] = 0;
                break;
                case "minor":
                    $c_version[1]++;
                    $c_version[2] = 0;
                break;
                case "revision":
                    $c_version[2]++;
                break;
                default:
                    echo "Sorry. Wrong param ".$version_segment.".\n";
                    die();
                break;
            }
            $this->_new_version_value = $c_version[0].".".$c_version[1].".".$c_version[2];
        }
    }
    
    protected function _showHelp()
    {
        $help_text = <<<EOT
Usage:
./deploy -h | --help            -> Show this help text
./deploy -f1.0.13               -> will force version number to 1.0.13
./deploy -vmajor                -> will increase to <2>.0.0
./deploy -vminor                -> will increase to 1.<1>.0
./deploy -vrevision             -> will increase to 1.0.<14>
./deploy -r                     -> will send changes to repositories           
./deploy -d                     -> will deploy to all hosts
./deploy --fake                 -> will simulate all process
./deploy -c                     -> will compact UI
                            
Examples:
./deploy -f1.0.13   -r          -> will force version number to 1.0.13 and send changes to repositories
./deploy -vrevision -r          -> will increase revision number and send changes to repos
./deploy -r --fake              -> will simulate sending changes to repos
./deploy -r -d                  -> will send changes to repos and deploy changes to all servers
./deploy -vrevision -c -r -d    -> will increase revision number, compact UI, send changes to repos and deploy changes to all servers

\n\n
EOT;
        
        echo $help_text;
    }
    
}