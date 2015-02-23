<?php
// vim: set ai ts=4 sw=4 ft=php:
if(!function_exists('music_list')) {
	include(__DIR__.'/functions.inc.php');
}
class Music implements BMO {

	public function __construct($freepbx = null) {
		if ($freepbx == null) {
			throw new Exception("Not given a FreePBX Object");
		}

		$this->FreePBX = $freepbx;
		$this->db = $freepbx->Database;
		$this->varlibdir = $freepbx->Config->get('MOHDIR');
		$this->mohdir = $freepbx->Config->get('ASTVARLIBDIR');
	}

	public function doConfigPageInit($page) {
		$request = $_REQUEST;
		$action = isset($request['action'])?$request['action']:'';
		$randon = isset($request['randon'])?$request['randon']:'';
		$randoff = isset($request['randoff'])?$request['randoff']:'';
		$category = isset($request['category'])?htmlspecialchars(strtr($request['category']," ./\"\'\`", "------")):'';
		$volume = isset($request['volume']) && is_numeric($request['volume']) ? $request['volume'] : '';

		// Determine default path to music directory, old default was mohmp3, now settable
		$path_to_moh_dir = $this->varlibdir.'/'.$this->mohdir;


		if ($category == null) $category = 'default';
		$display='music';


		if ($category == "default") {
			$path_to_dir = $path_to_moh_dir; //path to directory u want to read.
		} else {
			$path_to_dir = $path_to_moh_dir."/$category"; //path to directory u want to read.
		}


		if (strlen($randon)) {
			touch($path_to_dir."/.random");
			needreload();
		}
		if (strlen($randoff)) {
			unlink($path_to_dir."/.random");
			needreload();
		}
		switch ($action) {
			case "addednewstream":
			case "editednewstream":
				$stream = isset($request['stream'])?$request['stream']:'';
				$format = isset($request['format'])?trim($request['format']):'';
				if ($format != "") {
					$stream .= "\nformat=$format";
				}
				makestreamcatergory($path_to_dir,$stream);
				needreload();
			case "addednew":
				music_makemusiccategory($path_to_dir);
				needreload();
				//TODO: This needs to be removed when we fix BMO Redirects
				//redirect_standard();
			break;
			case "addedfile":
				needreload();
				//TODO: This needs to be removed when we fix BMO Redirects
		//		redirect_standard();
			break;
			case "delete":
				//$fh = fopen("/tmp/music.log","a");
				//fwrite($fh,print_r($_REQUEST,true));
				music_rmdirr("$path_to_dir");
				$path_to_dir = $path_to_moh_dir;
				$category='default';
				needreload();
			break;
			case 'getJSON':
				header('Content-Type: application/json');
				switch ($request['jdata']) {
					case 'music':
						$mohclass = $request['mohclass']?$request['mohclass']:'default';
						if ($mohclass == "default") {
							$path_to_dir = $this->mohdir; //path to directory u want to read.
						} else {
							$path_to_dir = $this->mohdir.'/'.$mohclass; //path to directory u want to read.
						}
						echo json_encode($this->fileList($path_to_dir));
						exit();
					break;
					case 'grid':
						echo json_encode(music_list());
						exit();
					break;
					default:
						echo json_encode(array('error' => _("Bad Query")));
						exit();
					break;
				}
			break;
		}

	}

	public function install() {

	}
	public function uninstall() {

	}
	public function backup(){

	}
	public function restore($backup){

	}
	public function genConfig() {

	}

	public function getAllMusic() {
		return music_list();
	}
	/**
	 * Get a list of mp3(MP3),wav(WAV) files from the provided directory.
	 * @param  string $path path to directory, webroot user must have read permissions 
	 * @return array       	list of files or null
	 */
	public function fileList($path){
		$pattern = '';
		$handle=opendir($path_to_dir) ;
		$extensions = array('mp3','MP3','wav','WAV'); // list of extensions to match
		//generate the pattern to look for.
		$pattern = '/(\.'.implode('|\.',$extensions).')$/i';
		//store file names that match pattern in an array
		$i = 0;
		while (($file = readdir($handle))!==false) {
			if ($file != "." && $file != "..") {
				if(preg_match($pattern,$file)) {
					$file_array[$i] = $file; //pattern is matched store it in file_array.
					$i++;
				}
			}
		}
		closedir($handle);
		return (isset($file_array))?$file_array:null;  //return the size of the array
	}
}
