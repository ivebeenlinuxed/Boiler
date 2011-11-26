<?php
define("BOILER_TMP", __DIR__."/../_offline/tmp/");
define("BOILER_LOCATION", __DIR__."/../framework/");


$software = json_decode(file_get_contents("softwarelib.json"));
switch ($argv[1]) {
	case "software":
		switch ($argv[2]) {
			case "list":
				foreach ($software as $s) {
					echo "{$s->name}\r\n";
				}
				break;
			case "install":
				if (!isset($argv[3])) {
					echo "Please specify package name\r\n";
					return;
				}
				
				if (($s = software_search_name($argv[3])) === false) {
					echo "Software not found\r\n";
					return;
				}
				
				if (is_installed($s->folder)) {
					echo "Software already installed\r\n";
					return;
				}
				
				
				$r = rand(0,10000);
				
				
				$ch = curl_init($s->file);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				file_put_contents(BOILER_TMP."software_$r.zip", curl_exec($ch));
				
				$zip = new ZipArchive;
				$res = $zip->open(BOILER_TMP."software_$r.zip");
				if ($res === TRUE) {
					mkdir($d = BOILER_LOCATION.'../_offline/tmp/software_'.$r);
				    $zip->extractTo($d);
				    $zip->close();
				   	software_install($d, $s->folder);
				} else {
				    echo 'Failed to open ZIP';
				}
				unlink($d);
				unlink(BOILER_TMP."software_$r.zip");
				mark_installed($s->folder);
				break;
			case "uninstall":
				if (!isset($argv[3])) {
					echo "Please specify package name\r\n";
					return;
				}
				
				if (($s = software_search_name($argv[3])) === false) {
					echo "Software not found\r\n";
					return;
				}
				
				if (!is_installed($s->folder)) {
					echo "Software is not installed\r\n";
					return;
				}
				
				
				software_uninstall($s->folder);
				break;
			default:
				echo "This expression needs a verb (install? list?)\r\n";
				break;
				
		}
}

function software_install($dir, $folder) {
	$dh = opendir($dir);
	while ($d = readdir($dh)) {
		switch ($d) {
			case "js":
				rename($dir."/".$d, BOILER_LOCATION."../htdocs/js/".$folder);
				break;
			case "img":
				rename($dir."/".$d, BOILER_LOCATION."../htdocs/img/".$folder);
				break;
			case "controller":
				rename($dir."/".$d, BOILER_LOCATION."application/controller/".$folder);
				break;
			case "view":
				rename($dir."/".$d, BOILER_LOCATION."application/view/".$folder);
				break;
			case "library":
				rename($dir."/".$d, BOILER_LOCATION."application/library/".$folder);
				break;
			case "model":
				rename($dir."/".$d, BOILER_LOCATION."application/model/".$folder);
				break;
			
		}
	}
}

function is_installed($folder) {
	$j = json_decode(file_get_contents("install.json"));
	if (isset($j->$folder) && $j->$folder != false) {
		return true;
	} else {
		return false;
	}
}

function mark_installed($folder) {
	$j = json_decode(file_get_contents("install.json"));
	$j->$folder = true;
	file_put_contents("install.json", json_encode($j));
}

function software_uninstall($folder) {
	$folders = array(BOILER_LOCATION."../htdocs/js/".$folder,
	BOILER_LOCATION."../htdocs/img/".$folder,
	BOILER_LOCATION."application/controller/".$folder,
	BOILER_LOCATION."application/view/".$folder,
	BOILER_LOCATION."application/library/".$folder,
	BOILER_LOCATION."application/model/".$folder
	);
	
	foreach ($folders as $f) {
		@unlink($f);
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
