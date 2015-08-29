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
		$this->config = $this->loadMoHConfig();
	}

	public function showPage() {

		$mh = $this;
		$heading = _("On Hold Music");
		$request = $_REQUEST;
		$request['view'] = isset($request['view'])?$request['view']:'';
		$request['action'] = isset($request['action'])?$request['action']:'';
		$request['category'] = isset($request['category'])?$this->stripCategory($request['category']):"";
		switch($request["action"]){
			case "edit":
			switch($request['type']) {
				case "custom":
					$data = $this->getCategoryByName($request['category']);
					dbug($data);
					$content = load_view(__DIR__.'/views/customform.php', array("data" => $data, "request" => $request));
				break;
				case "files":
					$media = \FreePBX::create()->Media;
					$supported = $media->getSupportedFormats();
					ksort($supported['in']);
					ksort($supported['out']);
					$supportedHTML5 = $media->getSupportedHTML5Formats();
					$convertto = array_intersect($supported['out'], $mh->convert);
					$data = $this->getCategoryByID($_REQUEST['id']);
					$heading .= ' - '.$data['category'];
					$path = $this->getCategoryPath($data['category']);
					$files = array();
					foreach($mh->fileList($path) as $f) {
						$i = pathinfo($f);
						$files[] = strtolower($i['filename']);
					}
					$content = load_view(__DIR__.'/views/filesform.php', array("files" => $files, "convertto" => $convertto, "supportedHTML5" => implode(",",$supportedHTML5), "supported" => $supported, 'data' => $data));
					$content .= load_view(__DIR__.'/views/musiclist.php', array('request' => $request, 'mh' => $mh));
				break;
			}
			break;
			case "add":
				switch($request['type']) {
					case "custom":
					$content = load_view(__DIR__.'/views/customform.php', array('request' => $request, 'mh' => $mh));

					break;
					case "files":
						$content = load_view(__DIR__.'/views/addcatform.php', array('request' => $request, 'mh' => $mh));
					break;
				}
			break;
			default:
				$content = load_view(__DIR__.'/views/grid.php', array('request' => $request, 'mh' => $mh));
			break;
		}

		$request["action"] = ($request["action"] == "delete") ? "" : $request["action"];
		return load_view(__DIR__.'/views/main.php', array('request' => $request, 'heading' => $heading, 'content' => $content));
	}

	public function doConfigPageInit($page) {
		$request = $_REQUEST;
		$action = isset($request['action']) ? $request['action'] : '';
		$rand = isset($request['erand']) && $request['erand'] == 'yes' ? true : false;
		$category = isset($request['category']) ? $this->stripCategory($request['category']) : '';
		$volume = isset($request['volume']) && is_numeric($request['volume']) ? $request['volume'] : '';

		// Determine default path to music directory, old default was mohmp3, now settable
		$path_to_moh_dir = $this->mohpath;


		if ($category == null) {
			$category = 'default';
		}
		$display='music';

		$path_to_dir = $this->getCategoryPath($category);

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
				if (!is_dir($path_to_dir)) {
					mkdir("$path_to_dir", 0755);
				}
				$fh=fopen("$path_to_dir/.custom","w");
				fwrite($fh,$stream);
				fclose($fh);
				needreload();
			break;
			case "addednew":
				mkdir("$path_to_dir", 0755, true);
				needreload();
			break;
			case "delete":
				if($path_to_dir != $path_to_moh_dir) {
					$this->rmdirr("$path_to_dir");
					needreload();
				}
				$path_to_dir = $path_to_moh_dir;
				$category='default';
			break;
		}
	}

	public function genConfig() {
		global $amp_conf;
		global $version; //asterisk version
		$path_to_moh_dir = $amp_conf['ASTVARLIBDIR'].'/'.$amp_conf['MOHDIR'];
		$output = "";

		$File_Write="";
		$tresults = \FreePBX::Music()->getAllMusic();
		$ccc = \FreePBX::Config()->get("CACHERTCLASSES") ? "yes" : "no";
		$File_Write = "[general]\ncachertclasses=".$ccc."\n";
		if (isset($tresults)) {

			$random = "sort=random\n";
			$alpha = "sort=alpha\n";

			foreach ($tresults as $tresult)  {
				if(strtolower($tresult) == "general") {
					continue;
				}
				// hack - but his is all a hack until redone, in functions, etc.
				// this puts a none category to allow no music to be chosen
				//
				if ($tresult == "none") {
					$dir = $path_to_moh_dir."/.nomusic_reserved";
					if (!is_dir($dir)) {
						mkdir("$dir", 0755);
					}
					touch($dir."/silence.wav");
				} elseif ($tresult != "default" ) {
					$dir = $path_to_moh_dir."/{$tresult}/";
				} else {
					$dir = $path_to_moh_dir.'/';
				}
				if (file_exists("{$dir}.custom")) {
					$application = file_get_contents("{$dir}.custom");
					$File_Write.="[{$tresult}]\nmode=custom\napplication=$application\n";
				} else if (file_exists("{$dir}.random")) {
					$File_Write.="[{$tresult}]\nmode=files\ndirectory={$dir}\n$random";
				} else {
					$File_Write.="[{$tresult}]\nmode=files\ndirectory={$dir}\n$alpha";
				}
			}
		}

		$conf = array();
		$conf["musiconhold_additional.conf"] = $File_Write;
		return $conf;
	}

	public function writeConfig($conf){
		$this->FreePBX->WriteConfig($conf);
	}

	public function rmdirr($path) {
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
			$this->rmdirr("$dirname/$entry");
		}

		// Clean up
		$dir->close();
		return rmdir($dirname);
	}

	public function install() {
		$sql = 'CREATE TABLE IF NOT EXISTS `music` (
			`id` INT NOT NULL AUTO_INCREMENT,
			`category` VARCHAR(255) NULL,
			`type` VARCHAR(100) NULL,
			`random` TINYINT NULL,
			`application` varchar(255) NULL,
			`format` varchar(5) NULL,
		PRIMARY KEY (`id`));';

		try {
			$check = $this->db->query($sql);
		} catch(\Exception $e) {
			die_freepbx("Can not execute $statement : " . $check->getMessage() .  "\n");
		}

		$freepbx_conf = $this->FreePBX->Config;
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

		$sql = "SELECT * FROM music WHERE category = 'default'";
		$sth = $this->db->prepare($sql);
		$sth->execute();
		$default = $sth->fetch(\PDO::FETCH_ASSOC);
		if(empty($default)) {
			$random = file_exists($this->mohpath."/.random") ? 1 : 0;
			$sql = "INSERT INTO music (`category`, `type`, `random`) VALUES (?,?,?)";
			$sth = $this->db->prepare($sql);
			$sth->execute(array("default","files",$random));
		}

		foreach(glob($this->mohpath."/*",GLOB_ONLYDIR) as $cat) {
			$category = basename($cat);
			$sql = "SELECT * FROM music WHERE category = ?";
			$sth = $this->db->prepare($sql);
			$sth->execute(array($category));
			$c = $sth->fetch(\PDO::FETCH_ASSOC);
			if(!empty($c)) {
				continue;
			}
			$random = file_exists($cat."/.random") ? 1 : 0;
			$application = "";
			$format = "";
			if(file_exists($cat."/.custom")) {
				$type = "custom";
				$application = file_get_contents($cat."/.custom");
				$application = explode("\n",$application);
				if (isset($application[1])) {
					$format = explode('=',$application[1],2);
					$format = $format[1];
				} else {
					$format = "";
				}
				$application = $application[0];
			} else {
				$type = "files";
			}

			$sql = "INSERT INTO music (`category`, `type`, `random`, `application`, `format`) VALUES (?,?,?,?,?)";
			$sth = $this->db->prepare($sql);
			$sth->execute(array($category,$type,$random,$application,$format));
		}
	}
	public function uninstall() {

	}
	public function backup(){

	}
	public function restore($backup){

	}

	public function getCategories(){
		$sql = "SELECT * FROM music";
		$sth = $this->db->prepare($sql);
		$sth->execute();
		$cats = $sth->fetchAll(\PDO::FETCH_ASSOC);
		return !empty($cats) ? $cats : array();
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
			case "deletemusic":
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
			case "deletemusic":
				if(empty($_POST['filename']) || empty($_POST['category'])) {
					return array("status" => false);
				}
				$category = isset($_POST['category'])?$this->stripCategory($request['category']):'';
				$filename = basename($_POST['filename']);

				// Determine default path to music directory, old default was mohmp3, now settable
				$path_to_moh_dir = $this->mohpath;

				if ($category == null) {
					$category = 'default';
				}
				$display='music';

				$path_to_dir = $this->getCategoryPath($category);

				if(file_exists($path_to_dir."/".$filename)) {
					unlink($path_to_dir."/".$filename);
				}
				return array("status" => true);
			break;
			case "upload":
				foreach ($_FILES["files"]["error"] as $key => $error) {
					switch($error) {
						case UPLOAD_ERR_OK:
							$extension = pathinfo($_FILES["files"]["name"][$key], PATHINFO_EXTENSION);
							$extension = strtolower($extension);
							$supported = $this->FreePBX->Media->getSupportedFormats();
							$category = isset($_REQUEST['category'])?$this->stripCategory($request['category']):'';
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
							$path .= '/'.basename($_REQUEST['category']);
						}
						$files = array();
						$count = 0;
						$list = $this->fileList($path);
						asort($list);
						foreach ($list as $value){
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

	public function getAllMusic($path=null) {
		if ($path === null) {
	    global $amp_conf;
	    // to get through possible upgrade gltiches, check if set
	    if (!isset($amp_conf['MOHDIR'])) {
	      $amp_conf['MOHDIR'] = '/mohmp3';
	    }
	    $path = $amp_conf['ASTVARLIBDIR'].'/'.$amp_conf['MOHDIR'];
	  }
		$i = 1;
		$arraycount = 0;
		$filearray = Array("default");

		if (is_dir($path)){
			if ($handle = opendir($path)){
				while (false !== ($file = readdir($handle))){
					if ( ($file != ".") && ($file != "..") && ($file != "CVS") && ($file != ".svn") && ($file != ".git") && ($file != ".nomusic_reserved" ) )
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
			// add a none categoy for no music
			if (!in_array("none",$filearray)) {
				$filearray[($i++)] = "none";
			}
			return ($filearray);
		} else {
			return null;
		}
	}

	/**
	 * Get a list of supported files from the provided directory.
	 * @param  string $path path to directory, webroot user must have read permissions
	 * @return array       	list of files or null
	 */
	public function fileList($path){
		$pattern = '';
		$handle = opendir($path) ;
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
		return (isset($file_array)) ? $file_array : array();  //return the size of the array
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

	public function getCategoryByName($name) {
		$sql = "SELECT * FROM music WHERE category = ?";
		$sth = $this->db->prepare($sql);
		$sth->execute(array($name));
		return $sth->fetch(\PDO::FETCH_ASSOC);
	}

	public function getCategoryByID($id) {
		$sql = "SELECT * FROM music WHERE id = ?";
		$sth = $this->db->prepare($sql);
		$sth->execute(array($id));
		return $sth->fetch(\PDO::FETCH_ASSOC);
	}

	/**
	 * Strip category of invalid characters
	 * @param  string $category [description]
	 * @return [type]           [description]
	 */
	private function stripCategory($category) {
		return basename(htmlspecialchars(strtr($category," ./\"\'\`", "------")));
	}

	private function getCategoryPath($category=null) {
		$path = $this->mohpath;
		if(!empty($category) && $category != 'default'){
			$path .= '/'.$category;
		}
		return $path;
	}

	private function loadMoHConfig() {
		$path = $this->FreePBX->Config->get('ASTETCDIR');
		if(file_Exists($path."/musiconhold_additional.conf")) {
			$lc = $this->FreePBX->LoadConfig("musiconhold_additional.conf");
			return $lc->ProcessedConfig;
		} else {
			return array();
		}
	}
}
