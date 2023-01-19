<?php

namespace UHC\task;

use UHC\utils\TextHelper;

use UHC\session\Session;
use UHC\session\SessionFactory;

use UHC\arena\team\Team;

use pocketmine\scheduler\Task;
use pocketmine\Server;

class PlayerNameTagUpdaterTask extends Task {

    /**
     * @return void
     */
    public function onRun() : void {
        foreach(Server::getInstance()->getOnlinePlayers() as $player){
            if(!$player->isConnected() || !($session = SessionFactory::getInstance()->getSession($player->getName())) instanceof Session){
                continue;
            }
            if(!($team = $session->getTeam()) instanceof Team){
                $player->setNameTag(TextHelper::replace(TextHelper::getMessageFile()->get("player-default-name-tag"), ["player_name" => $player->getName(), "heart" => round($player->getHealth(), 1)]));
            }else{
                $player->setNameTag(TextHelper::replace(TextHelper::getMessageFile()->get("player-default-team-name-tag"), ["player_name" => $player->getName(), "team" => $team->getColor(), "heart" => round($player->getHealth(), 1)]));
            }
            $player->setScoreTag(TextHelper::replace(TextHelper::getMessageFile()->get("player-default-score-tag"), ["device" => $session->getDevice(), "device_model" => $session->getDeviceModel()]));
        }
    }
}

?>