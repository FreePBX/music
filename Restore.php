<?php
namespace FreePBX\modules\Music;
use FreePBX\modules\Backup as Base;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Finder\Finder;
class Restore Extends Base\RestoreBase{
	public function runRestore(){
		$configs = $this->getConfigs();
		$files = $this->getFiles();
		foreach ($configs['data'] as $category) {
			$this->FreePBX->Music->addCategoryById($category['id'], $category['category'], $category['type']);
			$this->FreePBX->Music->updateCategoryById($category['id'], $category['type'], $category['random'], $category['application'], $category['format']);
		}
		$this->importAdvancedSettings($config['settings']);
		foreach ($files as $file) {
			$filename = $file->getPathTo().'/'.$file->getFilename();
			if(file_exists($filename)){
					continue;
			}
			copy($this->tmpdir.'/files/'.$file->getPathTo().'/'.$file->getFilename(), $filename);
		}
	}

	public function processLegacy($pdo, $data, $tables, $unknownTables){
		$this->restoreLegacyAdvancedSettings($pdo);

		if(version_compare_freepbx($this->getVersion(),"13","ge")) {
			$this->restoreLegacyDatabase($pdo);
		}
		if(!file_exists($this->tmpdir.'/var/lib/asterisk/moh')) {
			return;
		}

		$mohdir = $this->FreePBX->Config->get('ASTVARLIBDIR').'/'.$this->FreePBX->Config->get('MOHDIR');

		$finder = new Finder();
		$fileSystem = new Filesystem();
		foreach ($finder->in($this->tmpdir.'/var/lib/asterisk/moh') as $item) {
			if($item->isDir()) {
				$fileSystem->mkdir($mohdir.'/'.$item->getRelativePathname());
				continue;
			}
			$fileSystem->copy($item->getPathname(), $mohdir.'/'.$item->getRelativePathname(), true);
		}
	}
}
