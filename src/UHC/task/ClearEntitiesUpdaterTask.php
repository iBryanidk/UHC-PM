<?php

namespace UHC\task;

use UHC\utils\TextHelper;

use UHC\utils\Utils;
use pocketmine\Server;
use pocketmine\scheduler\Task;

class ClearEntitiesUpdaterTask extends Task {

    /** @var int */
    protected int $time = 0;

    /**
     * @return void
     */
    public function onRun() : void {
        if($this->time === 0){
            $this->time = 400;
            if(Utils::clearEntitiesFromWorld() > 0){
                Server::getInstance()->broadcastMessage(TextHelper::replace("&aEntities has been despawn from world"));
            }
        }
        $this->time--;
    }
}

?>