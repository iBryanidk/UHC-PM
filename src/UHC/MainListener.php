<?php

namespace UHC;

use UHC\utils\TextHelper;

use UHC\arena\team\Team;
use UHC\session\SessionFactory;

use pocketmine\event\Listener;

use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;

final class MainListener implements Listener {

    /**
     * @param PlayerJoinEvent $event
     * @return void
     */
    public function onPlayerJoinEvent(PlayerJoinEvent $event) : void {
        $player = $event->getPlayer();

        $session = SessionFactory::getInstance()->addSession($player->getName(), spl_object_id($player), $player->getUniqueId()->getBytes());
        $session->tryReconnect();
        $session->showCoordinates();

        $event->setJoinMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-join-to-server"), [
            "player_name" => $player->getName(),
        ]));
    }

    /**
     * @param PlayerQuitEvent $event
     * @return void
     */
    public function onPlayerQuitEvent(PlayerQuitEvent $event) : void {
        $player = $event->getPlayer();

        SessionFactory::getInstance()->removeSession($player->getName());

        $event->setQuitMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-quit-to-server"), [
            "player_name" => $player->getName(),
        ]));
    }

    /**
     * @param PlayerChatEvent $event
     * @return void
     */
    public function onPlayerChatEvent(PlayerChatEvent $event) : void {
        $player = $event->getPlayer();
        $message = $event->getMessage();

        $session = SessionFactory::getInstance()->getSession($player->getName());
        if(($team = $session->getTeam()) instanceof Team){
            $event->setFormat($session->isHost() ? TextHelper::replace("&r&7[&l&4HOST&r&7]&r {$team->getColor()} &7- &a{$player->getName()}&f: {$message}") : TextHelper::replace("{$team->getColor()} &7- &a{$player->getName()}&f: {$message}"));
        }else{
            $event->setFormat($session->isHost() ? TextHelper::replace("&r&7[&l&4HOST&r&7]&r &a{$player->getName()}&f: {$message}") : TextHelper::replace("&a{$player->getName()}&f: {$message}"));
        }
    }
}

?>