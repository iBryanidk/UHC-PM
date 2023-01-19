<?php

namespace UHC\arena\scenario;

use UHC\arena\game\GameArena;
use UHC\arena\game\utils\GameStatus;

use pocketmine\event\Listener;

abstract class Scenario implements Listener, GameScenarios {

    /**
     * @return string
     */
    abstract public function getName() : string;

    /**
     * @return string
     */
    abstract public function getDescription() : string;

    /**
     * @return bool
     */
    public function isActive() : bool {
        return in_array($this->getName(), GameArena::getInstance()->getScenarios()) && GameArena::getInstance()->getStatus() === GameStatus::RUNNING;
    }
}

?>