<?php

namespace UHC\utils;

final class Zip {

    /**
     * @param string $from
     * @param string $to
     * @param string $fileName
     * @return void
     */
    public static function compress(string $from, string $to, string $fileName) : void {
        $zip = new \ZipArchive;
        if(!is_dir($from)){
            @mkdir($from, 0755);
        }
        if(!is_dir($from.DIRECTORY_SEPARATOR.$fileName)){
            throw new \RuntimeException("Could not load world $fileName: File or directory not found");
        }
        $realPath = realpath($from.DIRECTORY_SEPARATOR.$fileName);

        if(!$zip->open($to.DIRECTORY_SEPARATOR.$fileName.".zip", $zip::CREATE | $zip::OVERWRITE)){
            throw new \RuntimeException("An error occurred while creating the zip file");
        }
        $dirFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($realPath), \RecursiveIteratorIterator::LEAVES_ONLY);
        foreach($dirFiles as $file){
            if(!$file->isDir()){
                $relativePath = $fileName."/".substr($file, strlen($realPath) + 1);
                $zip->addFile($file, $relativePath);
            }
        }
        $zip->close();
        unset($zip, $realPath, $files);
    }

    /**
     * @param string $from
     * @param string $to
     * @param string $fileName
     * @return void
     */
    public static function decompress(string $from, string $to, string $fileName) : void {
        if(!is_file($path = $from.DIRECTORY_SEPARATOR.$fileName.".zip")){
            throw new \RuntimeException("Could not load world $fileName: File or directory not found");
        }
        $zip = new \ZipArchive;
        $zip->open($path);
        if(!$zip->extractTo($to)){
            throw new \RuntimeException("An error occurred while unzipping the zip file");
        }
        $zip->close();
        unset($zip);
    }
}

?>