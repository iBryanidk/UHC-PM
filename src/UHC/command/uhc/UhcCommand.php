<?php

namespace UHC\command\uhc;

use UHC\utils\TextHelper;

use UHC\session\Session;
use UHC\session\SessionFactory;

use UHC\arena\team\Team;
use UHC\arena\team\TeamFactory;

use UHC\arena\game\utils\GamemodeType;
use UHC\arena\game\utils\GameScatter;

use UHC\arena\game\GameArena;
use UHC\arena\game\utils\GameStatus;
use UHC\arena\scenario\utils\GameSettingsForm;

use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class UhcCommand extends Command {

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
            $sender->sendMessage(TextHelper::replace("&cEnough arguments, please try '/team help'."));
            return;
        }
        $session = SessionFactory::getInstance()->getSession($sender->getName());
        switch($args[0]){
            case "start":
                if(!$session->isHost()){
                    $sender->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-no-it's-host")));
                    return;
                }
                if(GameArena::getInstance()->getStatus() !== GameStatus::PREPARING){
                    $sender->sendMessage(TextHelper::replace("&cYou can't run this command, game no is preparing"));
                    return;
                }
                GameArena::getInstance()->start();
            break;
            case "stop":
                if(!$session->isHost()){
                    $sender->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-no-it's-host")));
                    return;
                }
                GameArena::getInstance()->setStatus(GameStatus::ENDING);
            break;
            case "prepare":
                if(!$session->isHost()){
                    $sender->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-no-it's-host")));
                    return;
                }
                if(GameArena::getInstance()->getStatus() !== GameStatus::WAITING){
                    $sender->sendMessage(TextHelper::replace("&cYou can't run this command, game has started"));
                    return;
                }
                GameArena::getInstance()->prepare();
            break;
            case "settings":
                if(!$session->isHost()){
                    $sender->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-no-it's-host")));
                    return;
                }
                GameSettingsForm::getInstance()->settings($sender);
            break;
            case "host":
                if(count(array_filter(SessionFactory::getInstance()->getSessions(), fn(Session $session) => $session->isHost())) !== 0 && ($host = GameArena::getInstance()->getHost())->getPlayerNonNull() !== $session->getPlayerNonNull()){
                    $sender->sendMessage(TextHelper::replace("&c{$host->getName()} already hosting event"));
                    return;
                }
                if($session->isHost()){
                    $session->setHost();
                    
                    GameArena::getInstance()->setHost();
                    $sender->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-no-it's-host")));
                }else{
                    $session->setHost(true);

                    GameArena::getInstance()->setHost($session);
                    $sender->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-it's-host")));
                }
            break;
            case "revive":
                if(count($args) < 1){
                    $sender->sendMessage(TextHelper::replace("&c/uhc revive <name>"));
                    return;
                }
                if(GameArena::getInstance()->getStatus() !== GameStatus::RUNNING){
                    $sender->sendMessage(TextHelper::replace("&cAny game have started"));
                    return;
                }
                if(!($player = Server::getInstance()->getPlayerByPrefix($args[1])) instanceof Player){
                    $sender->sendMessage(TextHelper::replace("&cPlayer is offline"));
                    return;
                }
                $session = SessionFactory::getInstance()->getSession($player->getName());
                if(!GameScatter::getInstance()->scatterPlayer($session, true)){
                    $sender->sendMessage(TextHelper::replace("&cFailure on scattering of {$session->getName()}"));
                    return;
                }
                $sender->sendMessage(TextHelper::replace("You've revive &l&e{$player->getName()}"));
            break;
            case "reviveteam":
                if(count($args) < 1){
                    $sender->sendMessage(TextHelper::replace("&c/uhc reviveteam <id>"));
                    return;
                }
                if(GameArena::getInstance()->getStatus() !== GameStatus::RUNNING){
                    $sender->sendMessage(TextHelper::replace("&cAny game have started"));
                    return;
                }
                if(GameArena::getInstance()->getGamemodeType() !== ($gamemodeType = GamemodeType::TEAMS())){
                    $sender->sendMessage(TextHelper::replace("&cOnly available on gamemode ".$gamemodeType->__toString()));
                    return;
                }
                if(!($team = TeamFactory::getInstance()->get($args[1])) instanceof Team){
                    $sender->sendMessage(TextHelper::replace("&cTeam don't exists"));
                    return;
                }
                if(!GameScatter::getInstance()->scatterTeam($team, true)){
                    $sender->sendMessage(TextHelper::replace("&cFailure on scattering of {$team->getColor()}"));
                    return;
                }
                $sender->sendMessage(TextHelper::replace("You've revive &l&e{$team->getColor()}"));
            break;
            case "help":
            case "?":
                $sender->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("uhc-command-help")));
            break;    
            default:
                $sender->sendMessage(TextHelper::replace("&cEnough arguments, please try '/uhc help'."));
            break;    
        }
    }
}

?>