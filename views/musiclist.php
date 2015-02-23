<?php
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2015 Sangoma Technologies.
//
extract($request);
$file_array = array();
$file_array = music_build_list();
music_draw_list($file_array, $path_to_dir, $category);
