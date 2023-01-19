<?php

namespace UHC\arena\game;

use pocketmine\utils\SingletonTrait;

class GameSettings {
    use SingletonTrait;

    /** @var int */
    protected int $max_team_players = 2;

    /** @var int */
    protected int $max_keyboard_players_per_team = 1;

    /** @var int */
    protected int $apple_rate = 5;

    /**
     * @return int
     */
    public function getMaxTeamPlayers() : int {
       return $this->max_team_players;
    }

    /**
     * @param int $maxPlayers
     * @return void
     */
    public function setMaxTeamPlayers(int $maxPlayers) : void {
        $this->max_team_players = $maxPlayers;
    }

    /**
     * @return int
     */
    public function getMaxKeyboardPlayers() : int {
        return $this->max_keyboard_players_per_team;
    }

    /**
     * @param int $keyboardPlayers
     * @return void
     */
    public function setMaxKeyboardPlayers(int $keyboardPlayers) : void {
        $this->max_keyboard_players_per_team = $keyboardPlayers;
    }

    /**
     * @return int
     */
    public function getAppleRate() : int {
        return $this->apple_rate;
    }

    /**
     * @param int $appleRate
     * @return void
     */
    public function setAppleRate(int $appleRate) : void {
        $this->apple_rate = $appleRate;
    }
}

?>