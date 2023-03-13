<?php

namespace UHC\command;

use UHC\arena\game\GameArena;
use UHC\arena\game\utils\GameStatus;

use UHC\session\SessionFactory;
use UHC\utils\TextHelper;

use pocketmine\player\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class SpectateCommand extends Command {

    /**
     * @param string $name
     * @param string $description
     * @param string|null $permission
     * @param array $aliases
     */
    public function __construct(string $name, string $description, ?string $permission, array $aliases = []){
        parent::__construct($name, $description, "", $aliases);

        parent::setPermission($permission);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
        GameArena::getInstance()->setRunningTime(2090);
        if(!$sender instanceof Player){
            $sender->sendMessage(TextHelper::replace("&cRun this command in game"));
            return;
        }
        $session = SessionFactory::getInstance()->getSession($sender->getName());
        if(GameArena::getInstance()->getStatus() !== GameStatus::RUNNING){
            $sender->sendMessage(TextHelper::replace("&cAny game have started"));
            return;
        }
        if(($world = GameArena::getInstance()->getWorld()) === null){
            $sender->sendMessage(TextHelper::replace("&cAny game configure"));
            return;
        }
        if(!$session->isSpectador() && $world->getId() === $sender->getWorld()->getId()){
            $sender->sendMessage(TextHelper::replace("&cYou can't run this, you're playing"));
            return;
        }
        $session->setSpectador(true);
        $sender->teleport($world->getSafeSpawn());
    }
}

?>