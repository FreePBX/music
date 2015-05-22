<?php
namespace FreePBX\modules;
// vim: set ai ts=4 sw=4 ft=php:
if(!function_exists('music_list')) {
	include(__DIR__.'/functions.inc.php');
}

class Music implements \BMO {

	public function __construct($freepbx = null) {
		if ($freepbx == null) {
			throw new Exception("Not given a FreePBX Object");
		}

		$this->FreePBX = $freepbx;
		$this->db = $freepbx->Database;
		$this->mohdir = $freepbx->Config->get('MOHDIR');
		$this->varlibdir = $freepbx->Config->get('ASTVARLIBDIR');
		$this->mohpath = $this->varlibdir.'/'.$this->mohdir;
		$this->mpg123 = $freepbx->Config->get('AMPMPG123');
	}

	public function doConfigPageInit($page) {
		$request = $_REQUEST;
		$action = isset($request['action'])?$request['action']:'';
		$randon = isset($request['randon'])?$request['randon']:'';
		$randoff = isset($request['randoff'])?$request['randoff']:'';
		$category = isset($request['category'])?htmlspecialchars(strtr($request['category']," ./\"\'\`", "------")):'';
		$volume = isset($request['volume']) && is_numeric($request['volume']) ? $request['volume'] : '';

		// Determine default path to music directory, old default was mohmp3, now settable
		$path_to_moh_dir = $this->mohpath;


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
				music_makestreamcatergory($path_to_dir,$stream);
				needreload();
			case "addednew":
				music_makemusiccategory($path_to_dir);
				$_REQUEST['action'] = 'edit';
				$_REQUEST['category'] = $category;
			break;
			case "addedfile":
				$fileTypes = array("audio/wav", "audio/mpeg3");
				if(empty($_FILES)){
					$this->message = _("No file provided");
					break;
				}
				if(in_array($_FILES['mohfile']['type'], $fileTypes)){
				$file = $_FILES['mohfile'];
				if(move_uploaded_file($_FILES["mohfile"]["tmp_name"], $path_to_dir.'/'.$_FILES["mohfile"]["name"])){
					$this->message = _("File upload success");
				}else{
					$this->message = _("File seemed valid but could not move it to it's path");
				}

			}else{
				$this->message = _("Filetype not Supported Upload Failed");
				break;
			}
				needreload();
			break;
			case "delete":
				//$fh = fopen("/tmp/music.log","a");
				//fwrite($fh,print_r($_REQUEST,true));
				music_rmdirr("$path_to_dir");
				$path_to_dir = $path_to_moh_dir;
				$category='default';
				needreload();
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
			$dir = str_replace($this->mohpath.'/', '', $dir);
			if(empty($dir)){continue;}
			$cats[] = array('category' => $dir, 'type' => _('Standard'), 'link' => array('category' => urldecode($dir), 'type' =>'standard'));
		}
		if (file_exists($this->mohpath . '/.custom')) {
			$application = file_get_contents($this->mohpath.'/.custom');
			$application = explode("\n",$application);
			if (isset($application[0])) {
				$cats[] = array('category' => $application[0], 'type' => _('Streaming'), 'link' => array('category' => urldecode($application[0]), 'type' =>'streaming'));
			}
		}
		return $cats;
	}
	public function ajaxRequest($req, &$setting) {
		//The ajax request
		if ($req == "getJSON") {
			//Tell BMO This command is valid. If you are doing a lot of actions use a switch
			return true;
		}else{
			//Deny everything else
			return false;
		}
	}
	//This handles the AJAX via ajax.php?module=helloworld&command=getJSON&jdata=grid
	public function ajaxHandler() {
		if($_REQUEST['command'] == 'getJSON'){
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
					foreach ($this->fileList($path) as $value){
						$fp = pathinfo($path .'/'.$value);
						$oi = array('link' => array('category' => $_REQUEST['category'], 'filename' => $value));
						$files[] = array_merge($fp,$oi);
					}
					return $files;
				break;
				default:
					print json_encode(_("Invalid Request"));
				break;
			}
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
				$request['action'] = isset($requets['action'])?$request['action']:'';
				switch ($request['action']) {
					case 'add':
					case 'addstreaming':
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