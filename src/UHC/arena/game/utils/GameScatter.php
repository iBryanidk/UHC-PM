<?php

namespace UHC\arena\game\utils;

use UHC\Loader;

use UHC\event\GamePlayerJoinEvent;

use UHC\arena\team\Team;
use UHC\arena\team\TeamFactory;

use UHC\session\Session;
use UHC\session\SessionFactory;

use UHC\arena\game\GameArena;

use pocketmine\world\Position;
use pocketmine\entity\Location;

use pocketmine\utils\SingletonTrait;
use pocketmine\plugin\PluginException;

class GameScatter {
    use SingletonTrait;

    /**
     * @return void
     */
    public function scattering() : void {
        if(count(($scatting = $this->getScattingQueue())) <= 0){
            Loader::getInstance()->getLogger()->debug("Failure initializing scattering. Scattering Queue it's empty");
            return;
        }
        $start = microtime(true);
        foreach($scatting as $scatter){
            if($scatter->isScattering()){
                continue;
            }
            if($scatter instanceof Session){
                if(!$this->scatterPlayer($scatter)){
                    Loader::getInstance()->getLogger()->debug("Failure on scattering of {$scatter->getName()} {:::}");
                }
            }elseif($scatter instanceof Team){
                if(!$this->scatterTeam($scatter)){
                    Loader::getInstance()->getLogger()->debug("Failure on scattering of {$scatter->getColor()} {:::}");
                }
            }
        }
        Loader::getInstance()->getLogger()->info("Scattering finished in ".round(microtime(true) - $start, 3)."s");
    }

    /**
     * @param Session $session
     * @param bool $isForced
     * @return bool
     */
    public function scatterPlayer(Session $session, bool $isForced = false) : bool {
        if(($gamemode = GameArena::getInstance()->getGamemodeType()) !== GamemodeType::FFA()){
            throw new \LogicException("Currently gamemode is :".$gamemode->__toString());
        }
        if(($world = GameArena::getInstance()->getWorld()) === null){
            throw new PluginException("World can't be null ".__METHOD__);
        }
        if($session->isScattering() && !$isForced){
            return false;
        }
        $x = mt_rand(-GameArena::getInstance()->getBorder(), GameArena::getInstance()->getBorder());
        $z = mt_rand(-GameArena::getInstance()->getBorder(), GameArena::getInstance()->getBorder());
        if(!$world->isChunkGenerated($x, $z)){
            Loader::getInstance()->getLogger()->debug("Failure on scattering {$session->getName()} to {$x}::{$z}, trying again ...");

            // TRYING SCATTERING PLAYER AGAIN
            $this->scatterPlayer($session);
            return true;
        }
        $y = $world->getHighestBlockAt($x, $z) + 60;

        $position = new Position($x, $y, $z, $world);

        $session->setScattering(true);
        $session->getPlayerNonNull()->teleport(Location::fromObject($position, $world));

        (new GamePlayerJoinEvent($session->getPlayerNonNull(), $world))->call();
        return true;
    }

    /**
     * @param Team $team
     * @param bool $isForced
     * @return bool
     */
    public function scatterTeam(Team $team, bool $isForced = false) : bool {
        if(($gamemode = GameArena::getInstance()->getGamemodeType()) !== GamemodeType::TEAMS()){
            throw new \LogicException("Currently gamemode is :".$gamemode->__toString());
        }
        if(($world = GameArena::getInstance()->getWorld()) === null){
            throw new PluginException("World can't be null ".__METHOD__);
        }
        if($team->isScattering() && !$isForced){
            return false;
        }
        $x = mt_rand(-GameArena::getInstance()->getBorder(), GameArena::getInstance()->getBorder());
        $z = mt_rand(-GameArena::getInstance()->getBorder(), GameArena::getInstance()->getBorder());
        if(!$world->isChunkGenerated($x, $z)){
            Loader::getInstance()->getLogger()->debug("Failure on scattering {$team->getColor()} to {$x}::{$z}, trying again ...");

            // TRYING SCATTERING TEAM AGAIN
            $this->scatterTeam($team);
            return true;
        }
        $y = $world->getHighestBlockAt($x, $z) + 60;

        $position = new Position($x, $y, $z, $world);
        foreach($team->getOnlineMembers() as $member){
            if($member->isHost()){
                continue;
            }
            $member->setScattering(true);
            $member->getPlayerNonNull()->teleport(Location::fromObject($position, $world));

            (new GamePlayerJoinEvent($member->getPlayerNonNull(), $world))->call();
        }
        $team->setScattering(true);
        return true;
    }

    /**
     * @return Session[]|Team[]
     */
    protected function getScattingQueue() : array {
        return match(GameArena::getInstance()->getGamemodeType()){
            GamemodeType::FFA() => array_filter(SessionFactory::getInstance()->getSessions(), fn(Session $session) => !$session->isScattering()),
            GamemodeType::TEAMS() => array_filter(TeamFactory::getInstance()->getAll(), fn(Team $team) => !$team->isScattering()),
        };
    }
}


?>