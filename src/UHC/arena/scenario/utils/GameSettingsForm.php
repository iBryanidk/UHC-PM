<?php

namespace UHC\arena\scenario\utils;

use UHC\arena\game\utils\GameStatus;
use UHC\utils\TextHelper;

use UHC\arena\game\GameArena;
use UHC\arena\game\GameSettings;
use UHC\arena\game\utils\GamemodeType;

use UHC\arena\scenario\ScenarioFactory;

use UHC\api\form\BasicForm;
use UHC\api\form\CustomForm;

use pocketmine\Server;
use pocketmine\world\World;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

final class GameSettingsForm {
    use SingletonTrait;

    /**
     * @param Player $player
     * @return void
     */
    public function settings(Player $player) : void {
        $form = new BasicForm(function(Player $player, mixed $result) : void {
            if($result === null){
                return;
            }
            switch($result){
                case 0:
                    $this->gamemode_settings($player);
                break;
                case 1:
                    $this->game_settings($player);
                break;
                case 2:
                    $this->teams_settings($player);
                break;
                case 3:
                    $this->scenarios_settings($player);
                break;
                case 4:
                    $this->border_settings($player);
                break;
            }
        });
        $form->setTitle(TextHelper::replace("&l&dSETTINGS"));
        $form->addButton(TextHelper::replace("&l&6Gamemode settings"));
        $form->addButton(TextHelper::replace("&l&6World settings"));
        $form->addButton(TextHelper::replace("&l&6Teams settings"));
        $form->addButton(TextHelper::replace("&l&6Scenarios settings"));
        $form->addButton(TextHelper::replace("&l&6Border settings"));
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @return void
     */
    protected function gamemode_settings(Player $player) : void {
        $form = new CustomForm(function(Player $player, mixed $result) : void {
            if($result === null){
                return;
            }
            $result[0] = strtoupper($result[0]);
            if(($gamemodeType = GamemodeType::fromString(strtolower($result[0]))) === null){
                $player->sendMessage(TextHelper::replace("&cGamemode you're entering does not exist. Available: ".implode(", ", GamemodeType::getTypes())));
                return;
            }
            if(GameArena::getInstance()->getStatus() !== GameStatus::WAITING){
                $player->sendMessage(TextHelper::replace("&cYou can't run this command, game has started"));
                return;
            }
            GameArena::getInstance()->setGamemodeType($gamemodeType);
            $player->sendMessage(TextHelper::replace("You've configure &l&7UHC&r to run with &l&6{$result[0]}&r gamemode"));
        });
        $form->setTitle(TextHelper::replace("&l&6Gamemode settings"));
        $form->addInput(TextHelper::replace("&aGAMEMODE TYPE&f: "), TextHelper::replace("&7available: ".implode(", ", GamemodeType::getTypes())));
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @return void
     */
    protected function game_settings(Player $player) : void {
        $form = new CustomForm(function(Player $player, mixed $result) : void {
            if($result === null){
                return;
            }
            if(!Server::getInstance()->getWorldManager()->loadWorld($result[0])){
                $player->sendMessage(TextHelper::replace("&cWorld you're entering does exist"));
                return;
            }
            if(GameArena::getInstance()->getStatus() !== GameStatus::WAITING){
                $player->sendMessage(TextHelper::replace("&cYou can't run this command, game has started"));
                return;
            }
            GameArena::getInstance()->setWorld(($world = Server::getInstance()->getWorldManager()->getWorldByName($result[0])));
            GameSettings::getInstance()->setAppleRate($result[1]);
            $player->sendMessage(TextHelper::replace("You've configure &l&7UHC&r to run with &l&6{$world->getDisplayName()}&r world. And Apple Rate placed in &l&6{$result[1]}"));
        });
        $form->setTitle(TextHelper::replace("&l&6World settings"));
        $form->addInput(TextHelper::replace("&aWORLD&f: "), TextHelper::replace("&7Put name of world, where uhc will run"));
        $form->addSlider(TextHelper::replace("&aAPPLE RATE&f: "), 1, 100, -1, -1);
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @return void
     */
    protected function teams_settings(Player $player) : void {
        $form = new CustomForm(function(Player $player, mixed $result) : void {
            if($result === null){
                return;
            }
            if(GameArena::getInstance()->getStatus() !== GameStatus::WAITING){
                $player->sendMessage(TextHelper::replace("&cYou can't run this command, game has started"));
                return;
            }
            GameSettings::getInstance()->setMaxTeamPlayers($result[0]);
            GameSettings::getInstance()->setMaxKeyboardPlayers($result[1]);
            $player->sendMessage(TextHelper::replace("You've configure &l&7UHC&r with Team players &l&6{$result[0]}&r and Players with keyboard &l&6{$result[1]}"));
        });
        $form->setTitle(TextHelper::replace("&l&6Teams settings"));
        $form->addSlider(TextHelper::replace("Players per team&f: "), 1, 10, -1);
        $form->addSlider(TextHelper::replace("Players with keyboard per team&f: "), 1, 10, -1);
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @return void
     */
    protected function scenarios_settings(Player $player) : void {
        $form = new CustomForm(function(Player $player, mixed $result) : void {
            if($result === null){
                return;
            }
            $scenarios = array_values(ScenarioFactory::getInstance()->getAll());
            for($i = 0; $i < count($scenarios); $i++){
                $scenario = $scenarios[$i];

                $boolean = boolval($result[$i]);
                if($boolean){
                    GameArena::getInstance()->addScenario($scenario->getName());
                }else{
                    GameArena::getInstance()->removeScenario($scenario->getName());
                }
            }
            if(count(GameArena::getInstance()->getScenarios()) > 0){
                $player->sendMessage(TextHelper::replace("You've configure &l&7UHC&r with &l&6Scenarios&r ".implode(", ", GameArena::getInstance()->getScenarios())));
            }else{
                $player->sendMessage(TextHelper::replace("You've not configure &l&6Scenarios&r to &l&7UHC&r"));
            }
        });
        $form->setTitle(TextHelper::replace("&l&6Scenarios settings"));
        foreach(ScenarioFactory::getInstance()->getAll() as $scenario){
            $form->addToggle($scenario->getName(), in_array($scenario->getName(), GameArena::getInstance()->getScenarios()));
        }
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @return void
     */
    public function border_settings(Player $player) : void {
        $form = new CustomForm(function(Player $player, mixed $result) : void {
            if($result === null){
                return;
            }
            GameArena::getInstance()->setBorder($result[0]);
            $player->sendMessage(TextHelper::replace("You've configure &l&7UHC&r to Border &l&6{$result[0]}"));
        });
        $form->setTitle(TextHelper::replace("&l&6World settings"));
        $form->addSlider(TextHelper::replace("&aBORDER&f: "), 1, 1000, -1, -1);
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @return void
     */
    public function game_players_list(Player $player) : void {
        $form = new BasicForm(function(Player $player, mixed $result) : void {
            if($result === null){
                return;
            }
            if(!($playerClicked = Server::getInstance()->getPlayerExact($result)) instanceof Player){
                $player->sendMessage(TextHelper::replace("&cPlayer is offline"));
                return;
            }
            $player->teleport($playerClicked->getLocation());
            $player->sendMessage(TextHelper::replace("You've teleported to &l&6{$playerClicked->getName()}"));
        });
        $form->setTitle(TextHelper::replace("&l&6PLAYERS"));
        foreach(GameArena::getInstance()->getWorld()->getPlayers() as $online){
            if(!$online->isOnline() || !$online->isSurvival() || $online->getId() === $player->getId()){
                continue;
            }
            $form->addButton(TextHelper::replace("&l&6{$online->getName()}"), 0, "textures/ui/icon_steve", $online->getName());
        }
        $player->sendForm($form);
    }
}

?>