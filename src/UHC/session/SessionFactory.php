<?php

namespace UHC\session;

use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

class SessionFactory {
    use SingletonTrait;

    /** @var array<string, Session> */
    protected array $sessions = [];

    /** @var array<string, TemporaryOfflineSession> */
    protected array $offlineSessions = [];

    /**
     * @param string $name
     * @param int $id
     * @param string $rawUUID
     * @return Session
     */
    public function addSession(string $name, int $id, string $rawUUID) : Session {
        return $this->sessions[$name] = new Session($name, $id, $rawUUID);
    }

    /**
     * @param string $name
     * @return void
     */
    public function removeSession(string $name) : void {

        ($this->getSession($name))?->trySave();

        unset($this->sessions[$name]);
    }

    /**
     * @param string $name
     * @return Session|null
     */
    public function getSession(string $name) : ?Session {
        return $this->sessions[$name] ?? null;
    }

    /**
     * @return Session[]
     */
    public function getSessions() : array {
        return $this->sessions;
    }

    /**
     * @param Player $player
     * @return Player
     */
    public function getPlayerSession(Player $player) : Player {
        return $this->getSession($player)->getPlayerNonNull();
    }

    /**
     * @param Session $session
     * @return TemporaryOfflineSession
     */
    public function addOfflineSession(Session $session) : TemporaryOfflineSession {
        return $this->offlineSessions[$session->getName()] = new TemporaryOfflineSession($session);
    }

    /**
     * @param string $name
     * @return void
     */
    public function removeOfflineSession(string $name) : void {
        unset($this->offlineSessions[$name]);
    }

    /**
     * @param string $name
     * @return TemporaryOfflineSession|null
     */
    public function getOfflineSession(string $name) : ?TemporaryOfflineSession {
        return $this->offlineSessions[$name] ?? null;
    }

    /**
     * @return TemporaryOfflineSession[]
     */
    public function getOfflineSessions() : array {
        return $this->offlineSessions;
    }
}

?>