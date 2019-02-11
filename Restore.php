<?php
namespace FreePBX\modules\Music;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
	public function runRestore($jobid){
		$configs = $this->getConfigs();
		$files = $this->getFiles();
		print_r($configs);
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
		public function processLegacy($pdo, $data, $tables, $unknownTables, $tmpfiledir){
				$tables = array_flip($tables + $unknownTables);
				if (!isset($tables['music'])) {
						return $this;
				}
				$music = $this->FreePBX->Music;
				$music->setDatabase($pdo);
				$configs = $music->getCategories();
				$music->resetDatabase();
				$Directory = new \RecursiveDirectoryIterator($tmpfiledir);
				$Iterator = new \RecursiveIteratorIterator($Directory);
				$files = new \RegexIterator($Iterator, '/^.+\moh\//i', \RecursiveRegexIterator::GET_MATCH);
				foreach ($files as $path => $object) {
						@copy($path, $this->FreePBX->Config->get('ASTVARLIBDIR').'/moh/');
				}
				foreach ($configs as $category) {
						$this->FreePBX->Music->upsertCategoryById($category['id'], $category['type'], $category['random'], $category['application'], $category['format']);
				}
		}
}
