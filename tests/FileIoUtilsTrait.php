<?php

trait FileIoUtilsTrait {

    protected function rmdirRecursive($dir) {
        
        $iter = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
        $it = new RecursiveIteratorIterator($iter, RecursiveIteratorIterator::CHILD_FIRST);
        
        foreach($it as $file) {
            
            if ($file->isDir())  {
                
                rmdir($file->getPathname());
                
            } else {
                
                unlink($file->getPathname());
            }
        }
        
        rmdir($dir);
    }
}
