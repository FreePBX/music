<?php
namespace FreePBX\modules\Music;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FreePBX\modules\Backup as Base;
class Backup Extends Base\BackupBase{
  public function runBackup($id,$transaction){
    $dirs = [];
    $configs = $this->FreePBX->Music->getCategories();
    $varlibdir = $this->FreePBX->Config->get('ASTVARLIBDIR');
    $iterator = new RecursiveDirectoryIterator($varlibdir.'/moh',RecursiveDirectoryIterator::SKIP_DOTS);
    foreach (new RecursiveIteratorIterator($iterator) as $file) {
        $dirs[] = $file->getPath();
        $this->addFile($file->getBasename(),$file->getPath(),'',"moh");

    }
    $this->addDirectories($dirs);
    $this->addConfigs($configs);
  }
}