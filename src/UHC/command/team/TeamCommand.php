<?php

namespace UHC\command\team;

use UHC\utils\TextHelper;

use UHC\arena\team\Team;
use UHC\arena\team\TeamFactory;
use UHC\arena\team\TeamMember;

use UHC\arena\game\GameSettings;

use UHC\session\Session;
use UHC\session\SessionFactory;

use UHC\session\utils\Device;

use UHC\arena\game\GameArena;
use UHC\arena\game\utils\GamemodeType;
use UHC\arena\game\utils\GameStatus;

use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TE;

class TeamCommand extends Command {

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
        if(count($args) === 0){
            $sender->sendMessage(TextHelper::replace("&cEnough arguments, please try '/team help'."));
            return;
        }
        if(GameArena::getInstance()->getGamemodeType() !== ($gamemodeType = GamemodeType::TEAMS())){
            $sender->sendMessage(TextHelper::replace("&cGamemode no is ".$gamemodeType->__toString()." actually gamemode is ".GameArena::getInstance()->getGamemodeType()->__toString()));
            return;
        }
        $session = SessionFactory::getInstance()->getSession($sender->getName());
        switch($args[0]){
            case "create":
                if(GameArena::getInstance()->getStatus() !== GameStatus::WAITING){
                    $sender->sendMessage(TextHelper::replace("&cYou can't run this command, game has started"));
                    return;
                }
                if(($team = $session->getTeam()) instanceof Team){
                    $sender->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-have-team"), [
                        "team" => $team->getColor(),
                    ]));
                    return;
                }
                TeamFactory::getInstance()->add(($id = (count(TeamFactory::getInstance()->getAll()) + 1)), $sender->getName(), $sender->getXuid());

                TeamFactory::getInstance()->joinTeam($session, TeamFactory::getInstance()->get($id));

                Server::getInstance()->broadcastMessage(TE::colorize("&eTeam {$id}&r&f has been &acreated"));
            break;
            case "delete":
                if(GameArena::getInstance()->getStatus() !== GameStatus::WAITING){
                    $sender->sendMessage(TextHelper::replace("&cYou can't run this command, game has started"));
                    return;
                }
                if(!($team = $session->getTeam()) instanceof Team){
                    $sender->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-not-have-team")));
                    return;
                }
                if($team->getOwnerXuid() !== $sender->getXuid()){
                    $sender->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-are-not-owner-of-team"), [
                        "team" => $team->getColor(),
                    ]));
                    return;
                }
                Server::getInstance()->broadcastMessage(TE::colorize("&eTeam {$team->getId()}&r&f has been &cdeleted"));

                TeamFactory::getInstance()->remove($team->getId());
            break;
            case "leave":
                if(GameArena::getInstance()->getStatus() !== GameStatus::WAITING){
                    $sender->sendMessage(TextHelper::replace("&cYou can't run this command, game has started"));
                    return;
                }
                if(!($team = $session->getTeam()) instanceof Team){
                    $sender->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-not-have-team")));
                    return;
                }
                if($team->getOwnerXuid() === $sender->getXuid()){
                    $sender->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-are-owner-of-team"), [
                        "team" => $team->getColor(),
                    ]));
                    return;
                }
                $sender->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-has-been-leave-to-team"), [
                    "team" => $team->getColor(),
                ]));
            break;
            case "invite":
                if(GameArena::getInstance()->getStatus() !== GameStatus::WAITING){
                    $sender->sendMessage(TextHelper::replace("&cYou can't run this command, game has started"));
                    return;
                }
                if(!($team = $session->getTeam()) instanceof Team){
                    $sender->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-not-have-team")));
                    return;
                }
                if($team->getOwnerXuid() !== $sender->getXuid()){
                    $sender->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-are-not-owner-of-team"), [
                        "team" => $team->getColor(),
                    ]));
                    return;
                }
                if(count($args) < 1){
                    $sender->sendMessage(TextHelper::replace("&c/team invite <name>"));
                    return;
                }
                if(!($player = Server::getInstance()->getPlayerByPrefix($args[1])) instanceof Player){
                    $sender->sendMessage(TextHelper::replace("&cPlayer is offline"));
                    return;
                }
                (SessionFactory::getInstance()->getSession($player->getName()))->setTeamInvite($team);

                $player->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-has-been-invited-a-team"), [
                    "team" => $team->getColor(),
                ]));
                $sender->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-invited-a-player-to-team"), [
                    "player_name" => $player->getName(),
                    "team" => $team->getColor(),
                ]));
            break;
            case "accept":
                if(GameArena::getInstance()->getStatus() !== GameStatus::WAITING){
                    $sender->sendMessage(TextHelper::replace("&cYou can't run this command, game has started"));
                    return;
                }
                if(($team = $session->getTeam()) instanceof Team){
                    $sender->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-have-team"), [
                        "team" => $team->getColor(),
                    ]));
                    return;
                }
                if(!($team = $session->getTeamInvite()) instanceof Team){
                    $sender->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-not-has-been-invited-to-team")));
                    return;
                }
                if(count($team->getMembers()) === GameSettings::getInstance()->getMaxTeamPlayers()){
                    $sender->sendMessage(TextHelper::replace("&cTeam is full!"));
                    return;
                }
                if(in_array($session->getDevice(), Device::$unavailable)){
                    $keyboardPlayers = array_filter($team->getMembers(), fn(TeamMember $member) => in_array($member->getDevice(), Device::$unavailable));
                    if(count($keyboardPlayers) === GameSettings::getInstance()->getMaxKeyboardPlayers()){
                        $sender->sendMessage(TextHelper::replace("{$team->getColor()} reached the max keyboard players"));
                        return;
                    }
                }
                TeamFactory::getInstance()->joinTeam($session, $team);

                $sender->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-has-joined-to-team"), [
                    "team" => $team->getColor(),
                ]));
            break;
            case "kick":
                if(GameArena::getInstance()->getStatus() !== GameStatus::WAITING){
                    $sender->sendMessage(TextHelper::replace("&cYou can't run this command, game has started"));
                    return;
                }
                if(!($team = $session->getTeam()) instanceof Team){
                    $sender->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-not-have-team")));
                    return;
                }
                if($team->getOwnerXuid() !== $sender->getXuid()){
                    $sender->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-are-not-owner-of-team"), [
                        "team" => $team->getColor(),
                    ]));
                    return;
                }
                if(count($args) < 1){
                    $sender->sendMessage(TextHelper::replace("&c/team kick <name>"));
                    return;
                }
                if(!($player = Server::getInstance()->getPlayerByPrefix($args[1])) instanceof Player){
                    $sender->sendMessage(TextHelper::replace("&cPlayer is offline"));
                    return;
                }
                if(($playerSession = SessionFactory::getInstance()->getSession($player->getName())) instanceof Session && $playerSession->getTeam() instanceof Team && !$session->getTeam()->equals($playerSession->getTeam())){
                    $sender->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-teams-don't-match")));
                    return;
                }
                TeamFactory::getInstance()->removeTeam($player->getName(), $team);

                $player->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-has-been-kicked-from-team"), [
                    "team" => $team->getColor(),
                ]));
                $sender->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-kick-a-player-from-team"), [
                    "player_name" => $player->getName(),
                    "team" => $team->getColor(),
                ]));
            break;
            case "list":
                $teams = [];
                foreach(TeamFactory::getInstance()->getAll() as $team){
                    $members = [];
                    foreach($team->getMembers() as $member){
                        $members[] = ($member->isOnline() ? "&r&a{$member->getName()}" : "&r&c{$member->getName()}")." &7(&e{$member->getDevice()}&7)";
                    }
                    $teamPlayers = "&7(&f".count($members)."&7/&f".GameSettings::getInstance()->getMaxTeamPlayers()."&7)";
                    $teams[] = TextHelper::replace("{$team->getColor()} {$teamPlayers}&7 members&f: ".implode(", ", $members));
                }
                if(count($teams) === 0){
                    $sender->sendMessage(TextHelper::replace("&cNO TEAMS CREATED"));
                    return;
                }
                $sender->sendMessage(implode("\n", $teams));
            break;
            case "help":
            case "?":
                $sender->sendMessage(TextHelper::replace(TextHelper::getMessageFile()->get("team-command-help")));
            break;
            default:
                $sender->sendMessage(TextHelper::replace("&cEnough arguments, please try '/team help'."));
            break;
        }
    }
}

?>