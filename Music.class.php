<?php
namespace FreePBX\modules;
// vim: set ai ts=4 sw=4 ft=php:
if(!function_exists('music_list')) {
	include(__DIR__.'/functions.inc.php');
}

class Music implements \BMO {
	/** Extensions to show in the convert to section
	 * Limited on purpose because there are far too many,
	 * Most of which are not supported by asterisk
	 */
	public $convert = array(
		"wav",
		"sln",
		"g722",
		"ulaw",
		"alaw",
		"g729",
		"gsm",
		"wav49",
		"g719",
		"mp3"
	);

	public function __construct($freepbx = null) {
		if ($freepbx == null) {
			throw new Exception("Not given a FreePBX Object");
		}

		$this->FreePBX = $freepbx;
		$this->db = $freepbx->Database;
		$this->mohdir = $freepbx->Config->get('MOHDIR');
		$this->varlibdir = $freepbx->Config->get('ASTVARLIBDIR');
		$this->mohpath = $this->varlibdir.'/'.$this->mohdir;
	}

	public function doConfigPageInit($page) {
		$request = $_REQUEST;
		$action = isset($request['action'])?$request['action']:'';
		$rand = isset($request['erand']) && $request['erand'] == 'yes' ? true : false;
		$category = isset($request['category'])?htmlspecialchars(strtr($request['category']," ./\"\'\`", "------")):'';
		$volume = isset($request['volume']) && is_numeric($request['volume']) ? $request['volume'] : '';

		// Determine default path to music directory, old default was mohmp3, now settable
		$path_to_moh_dir = $this->mohpath;


		if ($category == null) {
			$category = 'default';
		}
		$display='music';


		if ($category == "default") {
			$path_to_dir = $path_to_moh_dir; //path to directory u want to read.
		} else {
			$path_to_dir = $path_to_moh_dir."/$category"; //path to directory u want to read.
		}


		if ($rand) {
			if(!file_exists($path_to_dir."/.random")) {
				touch($path_to_dir."/.random");
				needreload();
			}
		} else {
			if(file_exists($path_to_dir."/.random")) {
				unlink($path_to_dir."/.random");
				needreload();
			}
		}

		switch ($action) {
			case "addednewstream":
			case "editednewstream":
				$stream = isset($request['stream'])?$request['stream']:'';
				$format = isset($request['format'])?trim($request['format']):'';
				if ($format != "") {
					$stream .= "\nformat=$format";
				}
				music_makestreamcategory($path_to_dir,$stream);
				needreload();
			break;
			case "addednew":
				music_makemusiccategory($path_to_dir);
				$_REQUEST['action'] = 'edit';
				$_REQUEST['category'] = $category;
				needreload();
			break;
			case "updatecategory":
				$fileTypes = array("audio/wav", "audio/mpeg3");
				if(!empty($_FILES['mohfile']['type'])) {
					if(in_array($_FILES['mohfile']['type'], $fileTypes)){
						$file = $_FILES['mohfile'];
						if(move_uploaded_file($_FILES["mohfile"]["tmp_name"], $path_to_dir.'/'.$_FILES["mohfile"]["name"])){
							$this->message = _("File upload success");
							needreload();
						}else{
							$this->message = _("File seemed valid but could not move it to it's path");
						}
					}else{
						$this->message = _("Filetype not Supported Upload Failed");
						break;
					}
				}
			break;
			case "deletefile":
				$file = basename($_REQUEST['filename']);
				music_rmdirr("$path_to_dir"."/".$file);
			break;
			case "delete":
				if($path_to_dir != $path_to_moh_dir) {
					music_rmdirr("$path_to_dir");
					needreload();
				}
				$path_to_dir = $path_to_moh_dir;
				$category='default';
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
	public function getCategories(){
		$cats = array();
		$cats[] = array('category' => 'default', 'type' => _('Standard'), 'link' => array('category' => 'default', 'type' =>'standard'));
		foreach(glob($this->mohpath.'/*', GLOB_ONLYDIR) as $dir) {
			$type = file_exists($dir . '/.custom') ? _('Streaming') : _('Standard');
			$cats[] = array('category' => basename($dir), 'type' => $type, 'link' => array('category' => urldecode(basename($dir)), 'type' => file_exists($dir . '/.custom') ? 'streaming' : 'standard'));
		}

		return $cats;
	}

	public function ajaxRequest($req, &$setting) {
		$setting['authenticate'] = false;
		$setting['allowremote'] = false;
		switch($req) {
			case "gethtml5":
			case "playback":
			case "download":
			case "getJSON":
			case "upload":
				return true;
			break;
		}
		return false;
	}

	public function ajaxCustomHandler() {
		switch($_REQUEST['command']) {
			case "playback":
			case "download":
				$media = $this->FreePBX->Media();
				$media->getHTML5File($_REQUEST['file']);
			break;
		}
	}

	public function ajaxHandler() {
		switch($_REQUEST['command']) {
			case "upload":
				foreach ($_FILES["files"]["error"] as $key => $error) {
					switch($error) {
						case UPLOAD_ERR_OK:
							$extension = pathinfo($_FILES["files"]["name"][$key], PATHINFO_EXTENSION);
							$extension = strtolower($extension);
							$supported = $this->FreePBX->Media->getSupportedFormats();
							$category = isset($_REQUEST['category'])?htmlspecialchars(strtr($_REQUEST['category']," ./\"\'\`", "------")):'';
							if ($category == "default") {
								$path_to_dir = $this->mohpath; //path to directory u want to read.
							} else {
								$path_to_dir = $this->mohpath."/"; //path to directory u want to read.
							}
							if(in_array($extension,$supported['in'])) {
								$tmp_name = $_FILES["files"]["tmp_name"][$key];
								$dname = preg_replace("/\s+|'+|\"+|\?+|\*+/","-",strtolower($_FILES["files"]["name"][$key]));
								$name = pathinfo($dname,PATHINFO_FILENAME) . '.' . $extension;
								move_uploaded_file($tmp_name, $path_to_dir."/".$name);
								return array("status" => true, "name" => pathinfo($dname,PATHINFO_FILENAME), "filename" => $name, "type" => $extension, "category" => $category);
							} else {
								return array("status" => false, "message" => _("Unsupported file format"));
								break;
							}
						break;
						case UPLOAD_ERR_INI_SIZE:
							return array("status" => false, "message" => _("The uploaded file exceeds the upload_max_filesize directive in php.ini"));
						break;
						case UPLOAD_ERR_FORM_SIZE:
							return array("status" => false, "message" => _("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form"));
						break;
						case UPLOAD_ERR_PARTIAL:
							return array("status" => false, "message" => _("The uploaded file was only partially uploaded"));
						break;
						case UPLOAD_ERR_NO_FILE:
							return array("status" => false, "message" => _("No file was uploaded"));
						break;
						case UPLOAD_ERR_NO_TMP_DIR:
							return array("status" => false, "message" => _("Missing a temporary folder"));
						break;
						case UPLOAD_ERR_CANT_WRITE:
							return array("status" => false, "message" => _("Failed to write file to disk"));
						break;
						case UPLOAD_ERR_EXTENSION:
							return array("status" => false, "message" => _("A PHP extension stopped the file upload"));
						break;
					}
				}
				return array("status" => false, "message" => _("Can Not Find Uploaded Files"));
			break;
			case "gethtml5":
				$media = $this->FreePBX->Media();
				$path = $this->mohpath;
				if($_REQUEST['category'] != 'default'){
					$path .= '/'.$_REQUEST['category'];
				}
				$file = $path . "/" . $_REQUEST['file'];
				if (file_exists($file))	{
					$media->load($file);
					$files = $media->generateHTML5();
					$final = array();
					foreach($files as $format => $name) {
						$final[$format] = "ajax.php?module=music&command=playback&file=".$name;
					}
					return array("status" => true, "files" => $final);
				} else {
					return array("status" => false, "message" => _("File does not exist"));
				}
			break;
			case "getJSON":
				switch ($_REQUEST['jdata']) {
					case 'categories':
						return $this->getCategories();
					break;
					case 'musiclist':
						$path = $this->mohpath;
						if($_REQUEST['category'] != 'default'){
							$path .= '/'.$_REQUEST['category'];
						}
						$files = array();
						$count = 0;
						foreach ($this->fileList($path) as $value){
							$fp = pathinfo($value);
							$files[] = array('type' => $fp['extension'], 'category' => $_REQUEST['category'], 'id' => $count, 'filename' => $value , 'name' => $fp['filename']);
							$count++;
						}
						return $files;
					break;
					default:
						print json_encode(_("Invalid Request"));
					break;
				}
			break;
		}
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
		$handle=opendir($path) ;
		$supported = $this->FreePBX->Media->getSupportedFormats();
		$extensions = array_intersect($supported['out'], $this->convert);
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
		return (isset($file_array))?$file_array:array();  //return the size of the array
	}
	public function deleteFile($category, $filename){
		$path = $this->mohpath;
		if($category != 'default'){
			$path .= '/'.$category;
		}
		unlink($path . '/' . basename($filename));
	}
	public function getActionBar($request) {
		$buttons = array();
		switch($request['display']) {
			case 'music':
				$buttons = array(
					'delete' => array(
						'name' => 'delete',
						'id' => 'delete',
						'value' => _('Delete')
					),
					'reset' => array(
						'name' => 'reset',
						'id' => 'reset',
						'value' => _('Reset')
					),
					'submit' => array(
						'name' => 'submit',
						'id' => 'submit',
						'value' => _('Submit')
					)
				);
				if (empty($request['category'])||($request['category'] == 'default')) {
					unset($buttons['delete']);
				}
				$request['action'] = isset($request['action'])?$request['action']:'';
				switch ($request['action']) {
					case 'add':
					case 'edit':
					case 'updatecategory':
					case 'addstream':
					case 'editstream':
						/*if we match the above case(s) nothing to do*/
					break;
					default:
						/*If we don't match we return an empty array a.k.a no buttons*/
						$buttons = array();
					break;
				}
			break;
		}
		return $buttons;
	}
}
