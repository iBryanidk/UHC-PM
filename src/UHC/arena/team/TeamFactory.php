<?php

namespace UHC\arena\team;

use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat as TE;
use UHC\session\Session;
use UHC\session\SessionFactory;

class TeamFactory {
    use SingletonTrait;

    /** @var array<int, Team> */
    protected array $teams = [];

    /**
     * @param int $id
     * @param string $owner
     * @param string $ownerXuid
     * @return Team
     */
    public function add(int $id, string $owner, string $ownerXuid) : Team {
        $this->teams[$id] = new Team($id, $owner, $ownerXuid);

        return $this->teams[$id];
    }

    /**
     * @param int $id
     * @return void
     */
    public function remove(int $id) : void {
        foreach(($team = $this->get($id))->getMembers() as $member){
            if(!$team instanceof Team){
                continue;
            }
            $this->removeTeam($member->getName(), $team);
        }
        unset($this->teams[$id]);
    }

    /**
     * @param int $id
     * @return Team|null
     */
    public function get(int $id) : ?Team {
        return $this->teams[$id] ?? null;
    }

    /**
     * @return Team[]
     */
    public function getAll() : array {
        return $this->teams;
    }

    /**
     * @param Session $session
     * @param Team $team
     * @return void
     */
    public function joinTeam(Session $session, Team $team) : void {
        $session->setTeam($team);

        $team->addMember(TeamMember::instance($session->getPlayerNonNull()->getName(), $session->getDevice()));
    }

    /**
     * @param string $name
     * @param Team $team
     * @return void
     */
    public function removeTeam(string $name, Team $team) : void {
        if(($session = SessionFactory::getInstance()->getSession($name)) instanceof Session){
            $session->setTeam();
        }
        $team->removeMember($name);
    }

    /**
     * @param Session $s1
     * @param Session $s2
     * @return bool
     */
    public function equalsExact(Session $s1, Session $s2) : bool {
        $t1 = $s1->getTeam();
        $t2 = $s2->getTeam();
        return $t1 instanceof Team && $t2 instanceof Team && $t1->equals($t2);
    }
}

?>