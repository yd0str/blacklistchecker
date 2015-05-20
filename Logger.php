<?php
class Logger{

	public $logfile = "log.txt";
	
	public function logtofile($string, $start=0){
			//write to file
			if(!$handler = fopen($this->logfile, "a")){
				echo "can not open file";
			}
				
			$string = $string."<br>\n";
				
			if(fwrite($handler, $string)=== false){
				echo "can not write to file";
			}
			fclose($handler);
	}

	public function displayLog(){
		if(file_exists($this->logfile)){
			$output = file_get_contents($this->logfile);
			//auto refresh every 5s
			$url1=$_SERVER['REQUEST_URI'];
			header("Refresh: 5; URL=$url1");
		}
		return $output;
	}


	function loggerstart(){
			$logfile = $this->logfile;
			$output = "<br>New Log start at ".date("d.m.Y H:i:s")."<br>";
			$handler = fopen($logfile, "a");
			rewind($handler);//get to SOF
			fwrite($handler, $output);
			fclose($handler);
	}

}

//output of the log
$Logger = new Logger();
if(isset($_GET["show"]) and $_GET["show"]==1){
	echo $Logger->displayLog();


}