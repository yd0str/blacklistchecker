<?php 
ini_set('max_execution_time', 300); //300 seconds = 5 minutes *hate me later*
//ini_set('memory_limit', 128);     


//include_once 'WorkerThreads.php';
include_once 'Logger.php';

class items{
    public $path = "content";
    public $IPfile = "ips.txt";
    public $BlacklistFile = "list.txt";
    public $bigfile = "bigfile.txt";
}

class getblacklist{

    private $items;
    private $logger;
    
    public function getblacklist(){
       $this->items = new items();
       $this->logger = new Logger();
       
       //start logging
       $this->logger->loggerstart();
    }
    
    public function TimeToUpdate(){
		if (file_exists($this->items->IPfile)){
			$last_modified = date("Ymd", filemtime($this->items->IPfile) );
		}else{
			$last_modified = date("Ymd", time() - 86400); //yesterday
		}
		$today = date("Ymd");
		
			if ($last_modified < $today) {
				$this->Update();
			}
    }

    public function unlinkOLDBlacklist(){
        echo "Del old big Blacklist<br>";
        
        if(file_exists($this->items->bigfile)){
            unlink($this->items->bigfile);
        }
    }
    

    public function Update(){
		if (file_exists($this->items->IPfile)) {
			$this->logger->logtofile("Del old IP List<br>");
			unlink($this->items->IPfile);
		}

		$this->unlinkOLDBlacklist();
		$this->logger->logtofile("unlink <b>old</b> Blacklists in content Folder<br>");
    	$this->unlinkOLDlists();
    	$this->logger->logtofile("<br>Now updating ...<br>");
    	$this->listparser();
    	$this->logger->logtofile("done");
    }
    
    public function merge_file($files, $endfile){
        $creatEndfile = fopen($endfile, "a+");
        $output = "";
        $path = $this->items->path ."/*.txt";
        foreach (glob($path) as $filename) {
            $output .= file_get_contents($filename);
        }
        fwrite($creatEndfile, $output);
        fclose($creatEndfile);
    }
    
    public function split_file($file_name, $parts_num)   {
        $handle = fopen($file_name, 'rb');
        $file_size = filesize($file_name);
        $parts_size = floor($file_size / $parts_num);
        $modulus=$file_size % $parts_num;
        for($i=0; $i<$parts_num; $i++)  {
            if($modulus!=0 and $i==$parts_num-1){
                $parts[$i] = fread($handle,$parts_size+$modulus) or die("error reading file");
            } else{
                $parts[$i] = fread($handle,$parts_size) or die("error reading file");
            }
        }
        //close file handle
        fclose($handle) or die("error closing file handle");
    
        //writing to splited files
         for($i=0; $i<$parts_num; $i++) {
            $handle = fopen('splited_'.$i, 'wb') or die("error opening file for writing");
            fwrite($handle,$parts[$i]) or die("error writing splited file");
            }

        fclose($handle);
    }

    public function unlinkOLDlists(){
    $FileCountinDir = $this->countFilesinDIR($this->items->path);
    		for($i=1; $i <= $FileCountinDir; $i++){
    		    if (file_exists($this->items->path ."/".$i.".txt")){
					$this->logger->logtofile($this->items->path ."/".$i.".txt found and unlinked<br>");
    			     unlink($this->items->path ."/".$i.".txt");
    		    }
    		}
    }
    
    public function listparser(){
    	$file = fopen($this->items->BlacklistFile, "r");
    	$i = 1;
    	
    	while(! feof($file))  {
    	  $shortname = $i;
    	  $url = fgets($file);
    	  $i++;
    	  $this->PHPwget($url, $shortname);
    	  $this->logger->logtofile("Blacklist Number: ".$shortname ." written<br>");
        }
        $this->moveallWGETFiletoFilter();
    fclose($file);
    }
     
    public function PHPwget($url, $shortname){
        //File to save the contents to
    	$path = $this->items->path;
    	
        $filename = $path."/".$shortname.".txt";
        $fp = fopen ($filename, "w+");
    
        //Here is the file we are downloading, replace spaces with %20
        $ch = curl_init(str_replace(" ","%20",$url));
        curl_setopt($ch, CURLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0");
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.google.com/search?q=blacklist');
    
        
        //get random proxy form list
        //$f_contents = file("proxy.txt");
        //$line = $f_contents[rand(0, count($f_contents) - 1)];
        //$port = explode(" ", $line);
        //curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_NTLM);
        //curl_setopt($ch, CURLOPT_PROXY, $line);
        //curl_setopt($ch, CURLOPT_PROXYPORT, $port[1]);
    
        //give curl the file pointer so that it can write to it
        curl_setopt($ch, CURLOPT_FILE, $fp);
    
        $data = curl_exec($ch);//get curl response
    
        curl_close($ch);
    }
    
    public function moveallWGETFiletoFilter(){
        //merg all blacklist together
        $files = glob($this->items->path ."/*.txt");//add all Files in an array
        $bigfile = $this->items->bigfile;
        $this->logger->logtofile("<br>Merge all Blacklist together<br>");
        $this->merge_file($files, $bigfile);
        $this->logger->logtofile("Blacklist is now: ".filesize($bigfile)." Bytes<br>");
        
        //split files into 300kb junks
        $this->logger->logtofile("Split the big Blacklist into 300kb junks<br>");
        $parts = ( filesize($bigfile) / 300000 );
        $this->split_file($bigfile, $parts);
        
        //clean blacklistdir
        $this->logger->logtofile("unlink Blacklists in content Folder<br>");
        $this->unlinkOLDlists();
        
        //move all splited_* files to /content/
        $splitedfiles = glob("splited_*");//add all Files in an array
        foreach ($splitedfiles as $file) {
            $this->logger->logtofile("move ".$file." to ".$this->items->path."/".$file."<br>");
            rename($file, $this->items->path."/".$file);
        }
        
        $FilesCountInDir = $this->countFilesinDIR($this->items->path);
        
        $splitedfilesInContent = glob($this->items->path ."/splited_*");
        foreach ($splitedfilesInContent as $file) {
    		$this->filterIPs($file);
    	}
    	
    	/*
    	// Worker pool
    	$workers = array();
    	
    	// Initialize and start the threads
    	foreach ($splitedfilesInContent as $file) {
    		$workers[$file] = new WorkerThreads($file);
    		$workers[$file]->start();
    	}
    	//join the threads
    	if($workers[$file]->start()){
    		$workers[$file]->join();
    	}
    	*/
    	
    	//creat the final list
    	$filteredFile = $this->items->path ."/filtert";
    	$this->creatFinalList($filteredFile);
    }
    
   public function filterIPs($string){
        $handle = fopen($string, "r");
        $filteredFile = $this->items->path ."/filtert";
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $output = "";
        
        		$data = preg_match_all('/([0-9]{1,3}\.){3}[0-9]{1,3}/', $line, $matches);
            	foreach($matches[0] as $k => $v) {
            		//skipping know false positv
            		if($v == "8.8.8.8" or $v == "127.0.0.1"){
            			continue;
            		}
            	  $output .= "{$v}\n"; 
            	  file_put_contents($filteredFile, $output, FILE_APPEND); //this is danm time consumming. need pthread?
            	}
            }
        fclose($handle);
        }
    }
    
    public function creatFinalList($oldlist){
        if(file_exists($this->items->IPfile)){
            $this->logger->logtofile("remove old ".$this->items->IPfile ."<br>");
            unlink($this->items->IPfile);
        }
        rename($oldlist, $this->items->IPfile);
        $this->logger->logtofile("New ".$this->items->IPfile ." created.<br>");
        
        echo "cleaning the content Folder<br>";
        $splitedfilesInContent = glob($this->items->path ."/splited_*");
        foreach ($splitedfilesInContent as $file) {
            if(file_exists($file)){
                unlink($file);
            }
        }
        
        $this->unlinkOLDBlacklist();
    }

    public function search($searchfor){
    	// the following line prevents the browser from parsing this as HTML.
    	header('Content-Type: text/plain');
    
    	$contents = file_get_contents($this->items->IPfile);
    	$pattern = preg_quote($searchfor, '/');
    	$pattern = "/^.*$pattern.*\$/m";
    	if(preg_match_all($pattern, $contents, $matches)){
    	   echo "Found matches:\n";
    	   $this->logger->logtofile("Found matches:\n");
    	   echo implode("\n", $matches[0]);
    	}
    	else{
    	   echo "No matches found";
    	   $this->logger->logtofile("no matches found");
    	}
    }
    
    public function searchShort($searchfor){
    	header('Content-Type: text/plain');
    
    	$contents = file_get_contents($this->items->IPfile);
    	$pattern = preg_quote($searchfor, '/');
    	$pattern = "/^.*$pattern.*\$/m";
    	if(preg_match_all($pattern, $contents, $matches)){
    	   echo "1";
    	   $this->logger->logtofile("Found matches:\n");
    	}
    	else{
    	   echo "0";
    	   $this->logger->logtofile("no matches found");
    	}
    }
    
    public function getIfSet(&$value, $default = null) {
           if(isset($value)){
               $output = $value;
           }else{
                $output = $default;
           }
           return $output;
     }
    
    public function countFilesinDIR($dir){
    $output = 0; 
        if ($handle = opendir($dir)) {
            while (($file = readdir($handle)) !== false){
                if (!in_array($file, array('.', '..')) and !is_dir($dir.$file)) 
                    $output++;
            }
        }
    return $output;
    }

}

?>