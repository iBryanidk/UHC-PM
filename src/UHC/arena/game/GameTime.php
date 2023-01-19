<?php

namespace UHC\arena\game;

use UHC\arena\game\utils\GameBorder;

class GameTime extends GameBorder {

    /** @var int */
    const STARTING = 30;

    /** @var int */
    const RUNNING = 0;

    /** @var int */
    const ENDING = 20;

    /** @var int */
    const PREPARING = 600;

    /** @var int */
    protected int $startingTime = self::STARTING;

    /** @var int */
    protected int $runningTime = self::RUNNING;

    /** @var int */
    protected int $endingTime = self::ENDING;

    /** @var int */
    protected int $preparingTime = self::PREPARING;

    /**
     * @return int
     */
    public function getStartingTime() : int {
        return $this->startingTime;
    }

    /**
     * @return void
     */
    public function incrementStartingTime() : void {
        $this->startingTime += 1;
    }

    /**
     * @return void
     */
    public function decrementStartingTime() : void {
        $this->startingTime -= 1;
    }

    /**
     * @param int $startingTime
     * @return void
     */
    public function setStartingTime(int $startingTime = self::STARTING) : void {
        $this->startingTime = $startingTime;
    }

    /**
     * @return void
     */
    public function incrementRunningTime() : void {
        $this->runningTime += 1;
    }

    /**
     * @return void
     */
    public function decrementRunningTime() : void {
        $this->runningTime -= 1;
    }

    /**
     * @return int
     */
    public function getRunningTime() : int {
        return $this->runningTime;
    }

    /**
     * @param int $runningTime
     * @return void
     */
    public function setRunningTime(int $runningTime) : void {
        $this->runningTime = $runningTime;
    }

    /**
     * @return int
     */
    public function getEndingTime() : int {
        return $this->endingTime;
    }

    /**
     * @return void
     */
    public function incrementEndingTime() : void {
        $this->endingTime += 1;
    }

    /**
     * @return void
     */
    public function decrementEndingTime() : void {
        $this->endingTime -= 1;
    }

    /**
     * @param int $endingTime
     * @return void
     */
    public function setEndingTime(int $endingTime = self::ENDING) : void {
        $this->endingTime = $endingTime;
    }

    /**
     * @return int
     */
    public function getPreparingTime() : int {
        return $this->preparingTime;
    }

    /**
     * @return void
     */
    public function incrementPreparingTime() : void {
        $this->preparingTime += 1;
    }

    /**
     * @return void
     */
    public function decrementPreparingTime() : void {
        $this->preparingTime -= 1;
    }

    /**
     * @param int $preparingTime
     * @return void
     */
    public function setPreparingTime(int $preparingTime = self::PREPARING) : void {
        $this->preparingTime = $preparingTime;
    }
}

?>