<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
global $asterisk_conf;
global $amp_conf;
require_once(dirname(__FILE__).'/functions.inc.php');

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


$freepbx_conf =& freepbx_conf::create();
if ($freepbx_conf->conf_setting_exists('AMPMPG123')) {
	$freepbx_conf->remove_conf_setting('AMPMPG123');
}

// CACHERTCLASSES
//
$set['value'] = true;
$set['defaultval'] =& $set['value'];
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 3;
$set['module'] = 'music';
$set['category'] = 'System Setup';
$set['emptyok'] = 0;
$set['name'] = 'Cache MoH Classes';
$set['description'] = 'When enabled Asterisk will use 1 instance of moh class for all channels who are using it, decreasing consumable cpu cycles and memory in the process';
$set['type'] = CONF_TYPE_BOOL;
$freepbx_conf->define_conf_setting('CACHERTCLASSES',$set,true);
