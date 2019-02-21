<?php
namespace FreePBX\modules\Music;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
	public function runRestore($jobid){
		$configs = $this->getConfigs();
		$files = $this->getFiles();
		foreach ($configs as $category) {
			$this->FreePBX->Music->addCategoryById($category['id'], $category['category'], $category['type']);
			$this->FreePBX->Music->updateCategoryById($category['id'], $category['type'], $category['random'], $category['application'], $category['format']);
		}
		foreach ($files as $file) {
			$filename = $file->getPathTo().'/'.$file->getFilename();
			if(file_exists($filename)){
					continue;
			}
			copy($this->tmpdir.'/files/'.$file->getPathTo().'/'.$file->getFilename(), $filename);
		}
	}
		public function processLegacy($pdo, $data, $tables, $unknownTables){
			$this->restoreLegacyDatabase($pdo);
			$Directory = new \RecursiveDirectoryIterator($this->tmpdir);
			$Iterator = new \RecursiveIteratorIterator($Directory);
			$files = new \RegexIterator($Iterator, '/^.+\moh\//i', \RecursiveRegexIterator::GET_MATCH);
			foreach ($files as $path => $object) {
				copy($path, $this->FreePBX->Config->get('ASTVARLIBDIR').'/moh/');
			}
		}
}
