<?php

namespace UHC\arena\team;

use UHC\session\SessionFactory;

use pocketmine\Server;
use pocketmine\player\Player;

class TeamMember {

    /**
     * TeamMember Constructor.
     * @param string $name
     * @param string $device
     */
    public function __construct(
        protected string $name,
        protected string $device,
    ){}

    /**
     * @return string
     */
    public function getName() : string {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDevice() : string {
        return $this->device;
    }

    /**
     * @return bool
     */
    public function isOnline() : bool {
        return ($player = Server::getInstance()->getPlayerByPrefix($this->getName())) instanceof Player && !(SessionFactory::getInstance()->getSession($player->getName()))->isSpectador();
    }

    /**
     * @param string $name
     * @param string $device
     * @return static
     */
    public static function instance(string $name, string $device) : self {
        return new self($name, $device);
    }
}

?>