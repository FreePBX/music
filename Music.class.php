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
	}

	public function doConfigPageInit($page) {

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
}
