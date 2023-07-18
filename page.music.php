<?php /* $Id$ */
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2015 Sangoma Technologies.
//
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

echo FreePBX::Music()->showPage();
function music_return_bytes($val) {
    $val = trim((string) $val);
    $last = strtolower($val[strlen($val)-1]);
    
    // Validate the input to ensure it ends with 'G', 'M', or 'K'
    if (!in_array($last, ['g', 'm', 'k'])) {
        throw new InvalidArgumentException("Invalid input format. Must end with 'G', 'M', or 'K'.");
    }
    
    // Extract the numeric part of the input string
    $numericPart = (int)substr($val, 0, -1);
    
    $val = match ($last) {
        'g' => $numericPart * 1024 * 1024 * 1024,
        'm' => $numericPart * 1024 * 1024,
        'k' => $numericPart * 1024,
        default => $val,
    };
    
    return $val;
}
?>
<script>
var post_max_size = <?php echo music_return_bytes(ini_get('post_max_size'))?>;
var upload_max_filesize = <?php echo music_return_bytes(ini_get('upload_max_filesize'))?>;
var max_size = (upload_max_filesize < post_max_size) ? upload_max_filesize : post_max_size;
</script>
