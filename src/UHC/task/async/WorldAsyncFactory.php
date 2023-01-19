<?php

namespace UHC\task\async;

use UHC\Loader;

use pocketmine\world\World;
use pocketmine\player\Player;

use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\SingletonTrait;

class WorldAsyncFactory {
    use SingletonTrait;

    /**
     * @return void
     */
    public function reload() : void {
        Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() : void {
            foreach(glob(Loader::getInstance()->getDataFolder()."worlds".DIRECTORY_SEPARATOR."*") as $worldName){
                $worldName = basename($worldName, ".zip");

                if(($world = ($worldManager = Loader::getInstance()->getServer()->getWorldManager())->getWorldByName($worldName)) !== null){
                    if($world->isLoaded()){
                        if(count(($players = $world->getPlayers())) > 0){
                            $this->out($players, $worldManager->getDefaultWorld());
                        }
                        $worldManager->unloadWorld($world);
                    }
                }
                Loader::getInstance()->getServer()->getAsyncPool()->submitTask(new LoadWorldAsync(
                    Loader::getInstance()->getDataFolder()."worlds",
                    Loader::getInstance()->getServer()->getDataPath()."worlds",
                    $worldName,
                ));
            }
        }), 20);
    }

    /**
     * @return void
     */
    public function save() : void {
        Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() : void {
            foreach(array_diff(scandir(Loader::getInstance()->getServer()->getDataPath()."worlds"), [".", ".."]) as $worldName){
                if(str_contains($worldName, ".zip") || str_contains($worldName, ".rar") || ($worldManager = Loader::getInstance()->getServer()->getWorldManager())->getWorldByName($worldName) === $worldManager->getDefaultWorld()){
                    continue;
                }
                Loader::getInstance()->getServer()->getAsyncPool()->submitTask(new SaveWorldAsync(
                    Loader::getInstance()->getServer()->getDataPath()."worlds",
                    Loader::getInstance()->getDataFolder()."worlds",
                    $worldName,
                ));
            }
        }), 15);
    }

    /**
     * @param Player[] $players
     * @param World $world
     * @return void
     */
    protected function out(array $players, World $world) : void {
        foreach($players as $player){
            $player->teleport($world->getSafeSpawn());
        }
    }
}

?>