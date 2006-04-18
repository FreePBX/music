<?php

function music_list($path) {
	$i = 0;
	$arraycount = 0;
	
	if (is_dir($path)){
		if ($handle = opendir($path)){
			while (false !== ($file = readdir($handle))){ 
				if ( ($file != ".") && ($file != "..") && ($file != "CVS") && ($file != ".svn")  ) 
				{
					if (is_dir("$path/$file"))
						$filearray[($i++)] = "$file";
				}
			}
		closedir($handle); 
		}
	}
	if (isset($filearray)) {
		sort($filearray);
		return ($filearray);
	} else {
		return null;
	}
}

function music_rmdirr($dirname)
{
    // Sanity check
    if (!file_exists($dirname)) {
        return false;
    }
 
    // Simple delete for a file
    if (is_file($dirname)) {
        return unlink($dirname);
    }
 
    // Loop through the folder
    $dir = dir($dirname);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }
 
        // Recurse
        music_rmdirr("$dirname/$entry");
    }
 
    // Clean up
    $dir->close();
    return rmdir($dirname);
}

?>
