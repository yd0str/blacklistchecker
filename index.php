<?php
//includes
include_once 'getblackist.php';

class api{
    
    private $getblacklist;
    
    public function api(){
         //ini
        $this->getblacklist = new getblacklist();

        
    }
	
    public function starter(){
		$this->getblacklist->TimeToUpdate();
    }
    
    public function ForceUpdate(){
        $update = $this->getblacklist->getIfSet($_REQUEST['update']);
        if(is_null($update) !== true and $update == "12255555555555"){
            echo "Updating: <br>";
            $this->getblacklist->Update();
            die();
        }
    }
    
    public function RenderOutput(){
        $ip = $this->getblacklist->getIfSet($_REQUEST['ip']);
        $short = $this->getblacklist->getIfSet($_REQUEST['s']);
		$short2 = $this->getblacklist->getIfSet($_REQUEST['short']);
        if(is_null($ip) !== true and filter_var($ip, FILTER_VALIDATE_IP) == true){
            if((is_null($short) !== true  or is_null($short2) !== true  ) and ($short == "1" or $short2 == "1") ){
                $this->getblacklist->searchShort($ip);
            }else{
                echo "search for ".$ip." ";
                $this->getblacklist->search($ip);
            }
        }else{
			echo '<img src="logo.jpg" alt="blacklist"><br>';
            echo    '<form action="index.php" method="post">
                    <p><b>IP:</b> <input type="text" name="ip" />
                    <input type="submit" value="Submit IP"/></p>
                    </form>';
            echo	'<br>';
            echo    "Use of th API<br>
                    Example:<br>
                    <li><b>?ip=127.0.0.1</b></li>
                    <smaller>give your the search of the IP 127.0.0.1 in a human readable form</smaller><br>
                    <li><b>?ip=127.0.0.1&s=1</b></li>
                    <smaller>give your the search of the IP 127.0.0.1 in a maschine readable form(0 or 1)</smaller><br>";
			echo 'Source: <a href="https://github.com/wopot/blacklistchecker" target"_blank">https://github.com/wopot/blacklistchecker</a>';
            
        }
    }
    

}
//ini
$api = new api();

$api->starter();
$api->ForceUpdate();
$api->RenderOutput();


?>