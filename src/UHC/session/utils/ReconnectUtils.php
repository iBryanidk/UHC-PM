<?php

namespace UHC\session\utils;

use UHC\Loader;
use UHC\utils\TextHelper;

use UHC\session\Session;
use UHC\session\SessionFactory;

use UHC\arena\team\Team;
use UHC\arena\team\TeamFactory;

use UHC\arena\game\GameArena;
use UHC\arena\game\utils\GameStatus;
use UHC\arena\game\utils\GamemodeType;

use pocketmine\scheduler\ClosureTask;

trait ReconnectUtils {

    /**
     * @return void
     */
    public function tryReconnect() : void {
        if(GameArena::getInstance()->getStatus() === GameStatus::RUNNING){
            if(($offlineSession = SessionFactory::getInstance()->getOfflineSession($this->getPlayerNonNull()->getName())) === null){
                return;
            }
            $this->getPlayerNonNull()->sendMessage(TextHelper::replace("&eReconnecting ..."));

            // Attempts to reconnect the player if their temporary session is still saved
            Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($offlineSession) : void {
                if(!$this->getPlayerNonNull()->spawned || !$this->getPlayerNonNull()->isOnline()){
                    return;
                }
                if($offlineSession->getPosition() === null){
                    $this->getPlayerNonNull()->sendMessage(TextHelper::replace("&cFailure to trying reconnect"));
                    return;
                }
                $this->getPlayerNonNull()->sendMessage(TextHelper::replace("&aReconnecting successfully"));
                if(GameArena::getInstance()->getGamemodeType() === GamemodeType::TEAMS()){
                    $this->setTeam($offlineSession->getTeamInfo());
                }
                $this->getPlayerNonNull()->getInventory()->setContents($offlineSession->getInventoryContents());
                $this->getPlayerNonNull()->getArmorInventory()->setContents($offlineSession->getArmorContents());

                $this->getPlayerNonNull()->teleport($offlineSession->getPosition());
            }), 80);
        }
    }

    /**
     * @return void
     */
    public function trySave() : void {
        if(GameArena::getInstance()->getStatus() === GameStatus::RUNNING){
            if(($world = GameArena::getInstance()->getWorld()) === null){
                return;
            }
            if(!$this->isSpectador() && $world->getId() === $this->getPlayerNonNull()->getWorld()->getId()){
                $offlineSession = SessionFactory::getInstance()->addOfflineSession($this);
                $offlineSession->setPosition($this->getPlayerNonNull()->getPosition());

                $offlineSession->setInventoryContents($this->getPlayerNonNull()->getInventory()->getContents());
                $offlineSession->setArmorContents($this->getPlayerNonNull()->getArmorInventory()->getContents());

                $offlineSession->spawnZombie();
            }
        }else{
            if(GameArena::getInstance()->getGamemodeType() === GamemodeType::TEAMS()){
                if(($team = $this->getTeam()) instanceof Team){
                    if($team->getOwnerXuid() === $this->getPlayerNonNull()->getXuid()){
                        TeamFactory::getInstance()->remove($team->getId());
                    }
                }
            }
        }
    }
}

?>