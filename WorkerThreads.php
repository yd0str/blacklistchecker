<?php
class WorkerThreads extends Thread
{
    private $workerId;

    public function __construct($filename)  {
        $this->filename = $filename;
    }

    public function run() {
    	    $handle = fopen($this->filename, "r");
		    $filteredFile = "content/filtert".$this->filename;
		        if ($handle) {
		            while (($line = fgets($handle)) !== false) {
		                $output = "";
		        
		        		$data = preg_match_all('/([0-9]{1,3}\.){3}[0-9]{1,3}/', $line, $matches);
		            	foreach($matches[0] as $k => $v) {
		            	  $output .= "{$v}\n"; 
		            	  file_put_contents($filteredFile, $output, FILE_APPEND); 
		            	}
		            }
		        fclose($handle);
		        }
        
        
    }
}

?>