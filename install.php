<?php
global $asterisk_conf;
global $amp_conf;
require_once("modules/music/functions.inc.php");

// In case there is an old version as part of the upgrade process, we will derive the current path
//
$moh_subdir = isset($amp_conf['MOHDIR']) ? trim(trim($amp_conf['MOHDIR']),'/') : 'mohmp3';
$path_to_moh_dir = $amp_conf['ASTVARLIBDIR']."/$moh_subdir";

$File_Write="";
$tresults = music_list($path_to_moh_dir);
if (isset($tresults)) {
	foreach ($tresults as $tresult)  {
		if ($tresult == "default" ) {
			$dir = $path_to_moh_dir;
		} elseif ($tresult == "none") {
      $dir = $path_to_moh_dir."/.nomusic_reserved";
      if (!is_dir($dir)) {
        mkdir("$dir", 0755,true); 
      }
      touch($dir."/silence.wav");
    } else {
      $dir = $path_to_moh_dir."/{$tresult}/";
		}
		if (file_exists("{$dir}.random")) {
			$File_Write.="[{$tresult}]\nmode=files\ndirectory={$dir}\nrandom=yes\n";
		} else {
			$File_Write.="[{$tresult}]\nmode=files\ndirectory={$dir}\n";
		}
	}
}
$handle = fopen($amp_conf['ASTETCDIR']."/musiconhold_additional.conf", "w");

if (fwrite($handle, $File_Write) === FALSE) {
	echo _("Cannot write to file")." ($tmpfname)";
	exit;
}

fclose($handle);

needreload();

?>

