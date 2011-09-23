<?php
$software = json_decode(file_get_contents("softwarelib.json"));
switch ($argv[1]) {
	case "software":
		switch ($argv[2]) {
			case "list":
				foreach ($software as $s) {
					echo "{$s->name}\n";
				}
				break;
			case "install":
				if (!isset($argv[3])) {
					echo "Please specify package name";
					return;
				}
				
				if (($s = software_search_name($argv[3])) === false) {
					echo "Software not found";
					return;
				}
				
				//$ch = curl_init($s->file);
				//curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				//$z = curl_exec($ch);
				//$tmp = tmpfile();
				//fwrite($tmp, $z);
				//var_dump($software);
				$zip = new ZipArchive;
				$res = $zip->open($s->file);
				if ($res === TRUE) {
					mkdir($d = BOILER_LOCATION.'../_offline/tmp/'.rand(0,10000));
				    $zip->extractTo($d);
				    $zip->close();
				   	software_install($dir);
				} else {
				    echo 'failed';
				}
				break;
			default:
				echo "This expression needs a verb (install? list?)";
				break;
				
		}
}

function software_install($dir) {
	$dh = opendir($dir);
	while ($d = readdir($dh)) {
		switch ($d) {
			case "js":
				$folder = BOILER_LOCATION."../htdocs/js/";
				rename($dir."/".$d)
		}
	}
}

function software_search_name($name) {
	global $software;
	foreach ($software as $s) {
		if ($s->name == $name) {
			return $s;
		}
	}
	return false;
}
?>
