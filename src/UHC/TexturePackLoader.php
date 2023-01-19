<?php

namespace UHC;

use pocketmine\utils\SingletonTrait;

use pocketmine\Server;
use pocketmine\utils\Config;

class TexturePackLoader {
    use SingletonTrait;

    /**
     * @return void
     */
    public function load() : void {
        if(!$this->allowed()){
            return;
        }
        foreach(glob(Loader::getInstance()->getDataFolder()."resource_pack".DIRECTORY_SEPARATOR."*") as $path){
            $name = basename($path);

            if(!file_exists(Server::getInstance()->getDataPath()."resource_packs".DIRECTORY_SEPARATOR.$name.".zip")){
                $textures[$name] = $name;

                $this->zip($name);
                $this->save_resource_packs_config($name);
            }
        }
        if(isset($textures)){
            Server::getInstance()->getLogger()->warning("Loading texture packs ".implode(", ", $textures).".");
            Server::getInstance()->getLogger()->warning("Please restart the server to apply the changes.");
        }
    }

    /**
     * @return bool
     */
    protected function allowed() : bool {
        return boolval(Loader::getInstance()->getConfig()->getNested("plugin-config.enable-texture-pack"));
    }

    /**
     * @param string|null $fileName
     * @return void
     */
    protected function save_resource_packs_config(string $fileName = null) : void {
        $config = new Config(Server::getInstance()->getDataPath()."resource_packs".DIRECTORY_SEPARATOR."resource_packs.yml", Config::YAML);
        $latestData = $config->get("resource_stack");
        for($i = 0; $i < count(is_bool($latestData) ? [] : $latestData); $i++){
            if($latestData[$i] === $fileName.".zip"){
                unset($latestData[$i]);
            }
            $config->set("resource_stack", array_merge($latestData, [$fileName.".zip"]));
        }
        if(is_bool($latestData)){
            $config->set("resource_stack", [$fileName.".zip"]);
        }
        try {
            $config->save();
        } catch(\JsonException $exception){
            Loader::getInstance()->getLogger()->error($exception->getMessage());
        }
    }

    /**
     * @param string|null $fileName
     * @return void
     */
    protected function zip(string $fileName = null) : void {
        $zip = new \ZipArchive;
        if(file_exists(Server::getInstance()->getDataPath()."resource_packs".DIRECTORY_SEPARATOR.$fileName.".zip")){
            return;
        }
        if(!is_dir(Server::getInstance()->getDataPath()."resource_packs")){
            @mkdir(Server::getInstance()->getDataPath()."resource_packs", 0755);
        }
        if(!is_dir(Loader::getInstance()->getDataFolder()."resource_pack".DIRECTORY_SEPARATOR.$fileName)){
            throw new \RuntimeException("Could not load resource pack $fileName: File or directory not found");
        }
        $realPath = realpath(Loader::getInstance()->getDataFolder()."resource_pack".DIRECTORY_SEPARATOR.$fileName);
        if(!$zip->open(Server::getInstance()->getDataPath()."resource_packs".DIRECTORY_SEPARATOR.$fileName.".zip", $zip::CREATE)){
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
}

?>