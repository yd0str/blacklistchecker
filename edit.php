<?php
$list = "list.txt";

if($_POST['Submit']){
	$open = fopen($list,"w+");
	$text = $_POST['update'];
	fwrite($open, $text);
	fclose($open);
	
	echo "File updated.<br />"; 
	echo "File:<br />";
	$file = file($list);
	foreach($file as $text) {
		echo $text."<br />";
	}
}else{
	$file = file($list);
	echo "<form action=\"".$_SERVER['SCRIPT_NAME']."\" method=\"post\">";
	echo "<textarea Name=\"update\" cols=\"50\" rows=\"10\">";
		foreach($file as $text) {
			echo $text;
		} 
	echo "</textarea>";
	echo "<input name=\"Submit\" type=\"submit\" value=\"Update\" />\n
	</form>";
}
?>