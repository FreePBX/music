<? 
global $asterisk_conf;
require_once("modules/music/functions.inc.php");

$File_Write="";
$tresults = music_list($asterisk_conf['astvarlibdir']."/mohmp3");
if (isset($tresults)) {
	foreach ($tresults as $tresult)  {
		if ($tresult != "default" ) {
			$dir = $asterisk_conf['astvarlibdir']."/mohmp3/{$tresult}/";
		} else {
			$dir = $asterisk_conf['astvarlibdir']."/mohmp3/";
		}
		if (file_exists("{$dir}.random")) {
			$File_Write.="[{$tresult}]\nmode=files\ndirectory={$dir}\nrandom=yes\n";
		} else {
			$File_Write.="[{$tresult}]\nmode=files\ndirectory={$dir}\n";
		}
	}
}
$handle = fopen("/etc/asterisk/musiconhold_additional.conf", "w");

if (fwrite($handle, $File_Write) === FALSE) {
	echo _("Cannot write to file")." ($tmpfname)";
	exit;
}

fclose($handle);

needreload();

?>

