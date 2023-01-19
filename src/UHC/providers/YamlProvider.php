<?php

namespace UHC\providers;

use UHC\Loader;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

class YamlProvider {
    use SingletonTrait;

    /** @var Config */
    public static Config $messages;

    /**
     * @return void
     */
    public function gen() : void {
        if(!is_dir(Loader::getInstance()->getDataFolder()."/worlds/")){
            @mkdir(Loader::getInstance()->getDataFolder()."/worlds/");
        }
        foreach(Loader::getInstance()->getResources() as $resource => $fileInfo){
            Loader::getInstance()->saveResource($resource, $this->isDevelopmentVersion());
        }
        self::$messages = new Config(Loader::getInstance()->getDataFolder()."messages.yml", Config::YAML);
    }

    /**
     * @return bool
     */
    protected function isDevelopmentVersion() : bool {
        return true;
    }
}

?>