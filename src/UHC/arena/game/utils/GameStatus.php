<?php

namespace UHC\arena\game\utils;

use pocketmine\utils\TextFormat as TE;

final class GameStatus {

    /** @var int */
    const WAITING = 0;

    /** @var int */
    const PREPARING = 1;

    /** @var int */
    const STARTING = 2;

    /** @var int */
    const RUNNING = 3;

    /** @var int */
    const ENDING = 4;

    /** @var int */
    const UNKNOWN = -1;

    /**
     * @param int $id
     * @return string
     */
    public static function fromId(int $id) : string {
        return match($id){
            self::WAITING => TE::colorize("&7Waiting"),
            self::PREPARING => TE::colorize("&9Preparing"),
            self::STARTING => TE::colorize("&eStarting"),
            self::RUNNING => TE::colorize("&aRunning"),
            self::ENDING => TE::colorize("&cEnding"),
            self::UNKNOWN => TE::colorize("&4Unavailable game")
        };
    }
}

?>