<?php

namespace UHC\command;

use UHC\arena\game\GameArena;
use UHC\arena\scenario\ScenarioFactory;
use UHC\utils\TextHelper;

use pocketmine\player\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class ScenariosCommand extends Command {

    /**
     * @param string $name
     * @param string $description
     * @param string|null $permission
     * @param $alias
     */
    public function __construct(string $name, string $description, ?string $permission, $alias = []){
        parent::__construct($name, $description, "", $alias);

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
        $scenarios = [];
        foreach(ScenarioFactory::getInstance()->getAll() as $scenario){
            $scenarios[] = TextHelper::replace(TextHelper::getMessageFile()->get("uhc-scenarios-list"), [
                "scenario_name" => GameArena::getInstance()->getScenario($scenario->getName()) !== null ? "&a{$scenario->getName()}" : "&e{$scenario->getName()}",
                "scenario_description" => $scenario->getDescription(),
            ]);
        }
        if(count($scenarios) === 0){
            $sender->sendMessage(TextHelper::replace("&cThere don't have any registered scenario"));
            return;
        }
        $sender->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("uhc-scenarios-list-header")));
        $sender->sendMessage(implode("\n", $scenarios));
    }
}

?>