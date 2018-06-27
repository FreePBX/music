<?php
namespace FreePBX\modules\Music;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
  public function runRestore($jobid){
    $configs = $this->getConfigs();
    $files = $this->getFiles();
    foreach ($configs as $category) {
        $this->FreePBX->Music->upsertCategoryById($category['id'], $category['type'], $category['random'], $category['application'], $category['format']);
    }
    foreach ($files as $file) {
        $filename = $file['pathto'].'/'.$file['filename'];
        if(file_exist($filename)){
            continue;
        }
        copy($this->tmpdir.'/files/'.$file['pathto'].'/'.$file['filename'], $filename);
    }
  }
}