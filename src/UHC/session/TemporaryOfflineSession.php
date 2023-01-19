<?php

namespace UHC\session;

use UHC\utils\NBT;
use UHC\arena\team\Team;
use UHC\world\entities\Zombie;

use pocketmine\item\Item;
use pocketmine\world\Position;

class TemporaryOfflineSession {

    /** @var Position|null */
    protected ?Position $position = null;

    /** @var Team|null */
    protected ?Team $teamInfo = null;

    /** @var Item[] */
    protected array $inventoryContents = [];

    /** @var Item[] */
    protected array $armorContents = [];

    /**
     * TemporaryOfflineSession Constructor.
     * @param Session $session
     */
    public function __construct(
        protected Session $session,
    ){
        if(($team = $session->getTeam()) instanceof Team){
            $this->teamInfo = $team;
        }
    }

    /**
     * @return string
     */
    public function getName() : string {
        return $this->session->getName();
    }

    /**
     * @return Session
     */
    public function getOrigin() : Session {
        return $this->session;
    }

    /**
     * @return Position|null
     */
    public function getPosition() : ?Position {
        return $this->position;
    }

    /**
     * @param Position $position
     * @return void
     */
    public function setPosition(Position $position) : void {
        $this->position = $position;
    }

    /**
     * @return Team|null
     */
    public function getTeamInfo() : ?Team {
        return $this->teamInfo;
    }

    /**
     * @param Item[] $inventoryContents
     * @return void
     */
    public function setInventoryContents(array $inventoryContents) : void {
        $this->inventoryContents = $inventoryContents;
    }

    /**
     * @return Item[]
     */
    public function getInventoryContents() : array {
        return $this->inventoryContents;
    }

    /**
     * @param Item[] $armorContents
     * @return void
     */
    public function setArmorContents(array $armorContents) : void {
        $this->armorContents = $armorContents;
    }

    /**
     * @return Item[]
     */
    public function getArmorContents() : array {
        return $this->armorContents;
    }

    /**
     * @return void
     */
    public function spawnZombie() : void {
        $zombie = new Zombie($this->getOrigin()->getPlayerNonNull()->getLocation(), NBT::createBaseNBT($this->getOrigin()->getPlayerNonNull()->getPosition()->x, $this->getOrigin()->getPlayerNonNull()->getPosition()->y, $this->getOrigin()->getPlayerNonNull()->getPosition()->z));

        $zombie->setSession($this->getOrigin());
        $zombie->setDespawnTime();

        $zombie->spawnToAll();
    }
}

?>