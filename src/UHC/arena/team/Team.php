<?php

namespace UHC\arena\team;

use UHC\session\Session;
use UHC\session\SessionFactory;

use pocketmine\Server;
use pocketmine\player\Player;

use pocketmine\plugin\PluginException;
use pocketmine\utils\TextFormat as TE;

class Team {

    /** @var bool */
    protected bool $scattering = false;

    /**
     * Team Constructor.
     * @param int $id
     * @param string $owner
     * @param string $ownerXuid
     * @param array<string, TeamMember> $members
     */
    public function __construct(
        protected int $id,
        protected string $owner,
        protected string $ownerXuid,
        protected array $members = [],
    ){}

    /**
     * @return int
     */
    public function getId() : int {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getOwner() : string {
        return $this->owner;
    }

    /**
     * @return string
     */
    public function getOwnerXuid() : string {
        return $this->ownerXuid;
    }

    /**
     * @return Player
     */
    public function getOwnerNonNull() : Player {
        return Server::getInstance()->getPlayerExact($this->getOwner()) ?? throw new PluginException("Player is offline");
    }

    /**
     * @return bool
     */
    public function isScattering() : bool {
        return $this->scattering;
    }

    /**
     * @param bool $scattering
     * @return void
     */
    public function setScattering(bool $scattering = false) : void {
        $this->scattering = $scattering;
    }

    /**
     * @param TeamMember $member
     * @return void
     */
    public function addMember(TeamMember $member) : void {
        if($this->getMember($member->getName()) !== null){
            return;
        }
        $this->members[$member->getName()] = $member;
    }

    /**
     * @param string $name
     * @return void
     */
    public function removeMember(string $name) : void {
        if($this->getMember($name) === null){
            return;
        }
        unset($this->members[$name]);
    }

    /**
     * @param string $name
     * @return TeamMember|null
     */
    public function getMember(string $name) : ?TeamMember {
        return $this->members[$name] ?? null;
    }

    /**
     * @return TeamMember[]
     */
    public function getMembers() : array {
        return $this->members;
    }

    /**
     * @return Session[]
     */
    public function getOnlineMembers() : array {
        foreach($this->getMembers() as $member){
            if(!($player = Server::getInstance()->getPlayerByPrefix($member->getName())) instanceof Player){
                continue;
            }
            $players[] = SessionFactory::getInstance()->getSession($player->getName());
        }
        return $players ?? [];
    }

    /**
     * @return string
     */
    public function getColor() : string {
        return TE::colorize("&eTeam ".$this->getId());
    }

    /**
     * @param Team $team
     * @return bool
     */
    public function equals(self $team) : bool {
        return $this->getId() === $team->getId();
    }

    /**
     * @return int
     */
    public function getKills() : int {
        $kills = 0;
        foreach($this->getMembers() as $member){
            if(!($session = SessionFactory::getInstance()->getSession($member->getName())) instanceof Session){
                continue;
            }
            $kills += $session->getKills();
        }
        return $kills;
    }
}

?>