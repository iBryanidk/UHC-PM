<?php

namespace UHC\arena\game;

use UHC\Loader;
use UHC\utils\Time;
use UHC\utils\TextHelper;

use UHC\session\Session;
use UHC\session\SessionFactory;

use UHC\arena\team\Team;
use UHC\arena\team\TeamFactory;

use UHC\arena\game\utils\GameStatus;
use UHC\arena\game\utils\GamemodeType;

use UHC\api\discord\Discord;
use UHC\api\discord\DiscordEmbed;
use UHC\api\discord\DiscordMessage;

use pocketmine\Server;
use pocketmine\player\Player;

use pocketmine\world\World;
use pocketmine\world\Position;
use pocketmine\player\GameMode;

use pocketmine\plugin\PluginException;

use pocketmine\item\VanillaItems;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\sound\PopSound;

class GameArena extends GameTime {
    use SingletonTrait;

    /** @var World|null */
    protected ?World $world = null;

    /** @var GamemodeType|null */
    protected ?GamemodeType $gamemode = null;

    /** @var Session|null */
    protected ?Session $host = null;

    /** @var int */
    protected int $time = 0;

    /** @var int */
    protected int $status = GameStatus::WAITING;

    /** @var array */
    protected array $scenarios = [];

    /**
     * @return void
     */
    public function start() : void {
        if($this->getGamemodeType() === GamemodeType::FFA()){
            // When the GamemodeType is equal to FFA if the previous game was TEAMS delete all teams
            if(count(($teams = TeamFactory::getInstance()->getAll())) !== 0){
                foreach($teams as $team){
                    TeamFactory::getInstance()->remove($team->getId());
                }
            }
        }
        $this->setStatus(GameStatus::STARTING);
    }

    /**
     * @return void
     */
    public function prepare() : void {
        if($this->getWorld() === null){
            return;
        }
        $this->setStatus(GameStatus::PREPARING);

        $this->setStartingTime(self::STARTING);
        $this->setRunningTime(self::RUNNING);
        $this->setEndingTime(self::ENDING);

        $this->setBorder(self::DEFAULT_BORDER_SIZE);

        if(!Discord::log()) return;

        $discordMessage = new DiscordMessage();
        $discordMessage->setUsername(Discord::getUsername());
        $discordMessage->setURL(Discord::getURL());

        $discordEmbed = new DiscordEmbed();
        $discordEmbed->setTitle("UHC HOST");

        $scenarios = [];
        foreach($this->getScenarios() as $scenario){
            $scenarios[] = "- ".$scenario;
        }
        $message = [
            "Starting in: ".Time::getTimeToFullString($this->getPreparingTime()),
            " ",
            "Hosted by: ".(($host = GameArena::getInstance()->getHost()) !== null ? $host->getName() : "Unknown"),
            "Gamemode: ".$this->getGamemodeType()->__toString(),
            " ",
            "Scenarios: "."\n",
            implode("\n", $scenarios),
            " ",
            "`play.vitalmc.cf : 19132`"."\n",
            "[@everyone]",
        ];
        $discordEmbed->setColor(0xDF02F1);
        $discordEmbed->setDescription(implode("\n", $message));

        $discordMessage->setEmbed($discordEmbed);
        Discord::send($discordMessage);
    }

    /**
     * @return void
     */
    public function unprepared() : void {
        if(($world = $this->getWorld()) !== null){
            foreach($world->getPlayers() as $player){
                $player->getEffects()->clear();
                $player->getInventory()->clearAll();
                $player->getArmorInventory()->clearAll();

                $player->setGamemode(GameMode::SURVIVAL());

                $player->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
            }
            foreach(SessionFactory::getInstance()->getSessions() as $session){
                if(!$session->isScattering()){
                    continue;
                }
                $session->setScattering();
            }
            foreach(TeamFactory::getInstance()->getAll() as $team){
                if(!$team->isScattering()){
                    continue;
                }
                $team->setScattering();
            }
            $this->setStatus(GameStatus::WAITING);
        }
    }

    /**
     * @return World|null
     */
    public function getWorld() : ?World {
        return $this->world;
    }

    /**
     * @param World|null $world
     * @return void
     */
    public function setWorld(World $world = null) : void {
        $this->world = $world;
    }

    /**
     * @return GamemodeType
     */
    public function getGamemodeType() : GamemodeType {
        return $this->gamemode ?? GamemodeType::FFA();
    }

    /**
     * @param GamemodeType|null $gamemodeType
     * @return void
     */
    public function setGamemodeType(GamemodeType $gamemodeType = null) : void {
        $this->gamemode = $gamemodeType ?? GamemodeType::FFA();
    }

    /**
     * @return Session|null
     */
    public function getHost() : ?Session {
        return $this->host;
    }

    /**
     * @param Session|null $host
     * @return void
     */
    public function setHost(Session $host = null) : void {
        $this->host = $host;
    }

    /**
     * @return int
     */
    public function getStatus() : int {
        return $this->status;
    }

    /**
     * @param int $status
     * @return void
     */
    public function setStatus(int $status) : void {
        $this->status = $status;
    }

    /**
     * @param string $scenario
     * @return void
     */
    public function addScenario(string $scenario) : void {
        if($this->getScenario($scenario) !== null){
            return;
        }
        $this->scenarios[$scenario] = $scenario;
    }

    /**
     * @param string $scenario
     * @return void
     */
    public function removeScenario(string $scenario) : void {
        if($this->getScenario($scenario) === null){
            return;
        }
        unset($this->scenarios[$scenario]);
    }

    /**
     * @param string $scenario
     * @return string|null
     */
    public function getScenario(string $scenario) : ?string {
        return $this->scenarios[$scenario] ?? null;
    }

    /**
     * @return array
     */
    public function getScenarios() : array {
        return $this->scenarios;
    }

    /**
     * @param string $message
     * @return void
     */
    public function broadcastMessage(string $message) : void {
        if(($world = $this->getWorld()) === null){
            throw new PluginException("World can't be null ".__METHOD__);
        }
        foreach($world->getPlayers() as $player){
            $player->sendMessage($message);
        }
    }

    /**
     * @param string $title
     * @param string $subtitle
     * @return void
     */
    public function broadcastTitle(string $title, string $subtitle = "") : void {
        if(($world = $this->getWorld()) === null){
            throw new PluginException("World can't be null ".__METHOD__);
        }
        foreach($world->getPlayers() as $player){
            $player->sendTitle($title, $subtitle);
        }
    }

    /**
     * @return Player[]
     */
    public function getRemainingPlayers() : array {
        if(($world = $this->getWorld()) === null){
            throw new PluginException("World can't be null ".__METHOD__);
        }
        return array_filter($world->getPlayers(), fn(Player $player) => $player->isSurvival(true));
    }

    /**
     * @return Team[]
     */
    public function getRemainingTeams() : array {
        return array_filter(TeamFactory::getInstance()->getAll(), fn(Team $team) => count($team->getOnlineMembers()) !== 0);
    }

    /**
     * @return bool
     */
    public function isInGracePeriod() : bool {
        return $this->getStatus() === GameStatus::RUNNING && $this->getRunningTime() <= 40;
    }

    /**
     * @return bool
     */
    public function hasInvincibility() : bool {
        return $this->getStatus() === GameStatus::RUNNING && $this->getRunningTime() <= 1200;
    }

    /**
     * @return void
     */
    public function heal() : void {
        if(($world = $this->getWorld()) === null){
            throw new PluginException("World can't be null ".__METHOD__);
        }
        foreach($world->getPlayers() as $player){
            $player->setHealth($player->getMaxHealth());
            $player->getWorld()->addSound($player->getPosition(), new PopSound());

            $player->getInventory()->addItem(VanillaItems::GOLDEN_APPLE()->setCount(10));
            $player->sendMessage(TextHelper::replace("&7(&l&c!&r&7)&r&a Your health has been regenerated"));
        }
    }

    /**
     * @param Session $session
     * @return void
     */
    protected function check(Session $session) : void {
        if(!$session->getTeam() instanceof Team){
            TeamFactory::getInstance()->add(($id = (count(TeamFactory::getInstance()->getAll()) + 1)), $session->getPlayerNonNull()->getName(), $session->getPlayerNonNull()->getXuid());

            TeamFactory::getInstance()->joinTeam($session, TeamFactory::getInstance()->get($id));
        }
    }

    /**
     * @param Player $player
     * @param World $world
     * @return void
     */
    protected function findBorder(Player $player, World $world) : void {
        $x = mt_rand(-GameArena::getInstance()->getBorder(), GameArena::getInstance()->getBorder());
        $z = mt_rand(-GameArena::getInstance()->getBorder(), GameArena::getInstance()->getBorder());
        if(!$world->isChunkGenerated($x, $z)){
            Loader::getInstance()->getLogger()->debug("Failure on border teleport to {$x}::{$z}, trying again ...");

            // TRYING TO FIND A BORDER AGAIN
            $this->findBorder($player, $world);
            return;
        }
        $y = $world->getHighestBlockAt($x, $z) + 2;
        $position = new Position($x, $y, $z, $world);

        $player->teleport($position);
    }

    /**
     * @return void
     */
    public function teleportToRoom() : void {
        if(($world = GameArena::getInstance()->getWorld()) === null){
            return;
        }
        foreach(Server::getInstance()->getOnlinePlayers() as $player){
            if($this->getGamemodeType() === GamemodeType::TEAMS()){
                if(($session = SessionFactory::getInstance()->getSession($player->getName())) instanceof Session){
                    // This should prevent when GamemodeType equals TEAMS players entering without having a team
                    $this->check($session);
                }
            }
            $player->teleport($world->getSafeSpawn());
        }
    }

    /**
     * @return void
     */
    public function teleportToBorder() : void {
        if(($world = $this->getWorld()) === null){
            throw new PluginException("World can't be null ".__METHOD__);
        }
        $spawn = $world->getSafeSpawn();
        foreach($world->getPlayers() as $player){
            $distance = $this->getDistance($spawn, $player->getPosition());
            if($distance > $this->getBorder()){
                $this->findBorder($player, $world);
            }
        }
    }

    /**
     * @return array
     * @phpstan-return <int, string>
     */
    public function findWinner() : array {
        $winnerData = [];
        switch($this->getGamemodeType()){
            case GamemodeType::FFA():
                if(count(($players = $this->getRemainingPlayers())) === 1){
                    /** @var Player $winner */
                    $winner = array_values($players)[0];

                    $winnerData[] = " &e".$winner->getName();
                    $winnerData[] = " &rTotal kills&f: ".(SessionFactory::getInstance()->getSession($winner->getName()))->getKills();
                }
            break;
            case GamemodeType::TEAMS():
                if(count(($teams = $this->getRemainingTeams())) === 1){
                    /** @var Team $winner */
                    $winner = array_values($teams)[0];

                    $winnerData[] = "&e ".$winner->getColor();
                    $winnerData[] = " &rTotal kills&f: ".$winner->getKills();
                    $winnerData[] = " &rMembers&f: ";
                    foreach($winner->getMembers() as $member){
                        $winnerData[] = " - ".($member->isOnline() ? "&r&a{$member->getName()}" : "&r&c{$member->getName()}")." &7(&e{$member->getDevice()}&7)";
                    }
                }
            break;
        }
        return $winnerData;
    }
}

?>