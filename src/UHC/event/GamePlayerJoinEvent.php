<?php

namespace UHC\event;

use pocketmine\world\World;
use pocketmine\player\Player;

use pocketmine\event\player\PlayerEvent;

class GamePlayerJoinEvent extends PlayerEvent {

    /** @var World */
    protected World $world;

    /**
     * GamePlayerJoinEvent Constructor
     * @param Player $player
     * @param World $world
     */
    public function __construct(
        Player $player,
        World $world,
    ){
        $this->player = $player;
        $this->world = $world;
    }

    /**
     * @return World
     */
    public function getWorld() : World {
        return $this->world;
    }
}

?>