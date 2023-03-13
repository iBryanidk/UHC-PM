<?php

namespace UHC\command;

use UHC\utils\TextHelper;
use UHC\task\async\WorldAsyncFactory;

use pocketmine\player\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class ReloadWorldsCommand extends Command {

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
        if(!$sender instanceof Player){
            $sender->sendMessage(TextHelper::replace("&cRun this command in game"));
            return;
        }
        if(!$sender->hasPermission($this->getPermission())){
            $sender->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("no-permission")));
            return;
        }
        if(count($args) === 0){
            $sender->sendMessage(TextHelper::replace("&cTry: /reloadworlds <reload|save>"));
            return;
        }
        switch($args[0]){
            case "reload":
                WorldAsyncFactory::getInstance()->reload();
            break;
            case "save":
                WorldAsyncFactory::getInstance()->save();
            break;
        }
    }
}

?>