<?php

namespace UHC\world\entities;

use UHC\Loader;
use UHC\session\Session;
use UHC\session\SessionFactory;

use UHC\arena\scenario\GameScenarios;
use UHC\arena\scenario\ScenarioFactory;

use UHC\arena\team\Team;

use UHC\arena\game\GameArena;
use UHC\arena\game\utils\GamemodeType;

use UHC\task\ZombieUpdaterTask;
use UHC\utils\TextHelper;

use pocketmine\Server;
use pocketmine\player\Player;

use pocketmine\scheduler\TaskHandler;

use pocketmine\entity\Location;
use pocketmine\entity\EntitySizeInfo;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class Zombie extends \pocketmine\entity\Zombie {

    /** @var int */
    protected int $time = 0;

    /** @var Session|null */
    protected ?Session $session = null;

    /** @var TaskHandler|null */
    protected ?TaskHandler $taskHandler = null;

    /**
     * @param Location $location
     * @param CompoundTag|null $nbt
     */
    public function __construct(Location $location, ?CompoundTag $nbt = null){
        parent::__construct($location, $nbt);

        $this->setNameTagVisible();
        $this->setNameTagAlwaysVisible();

        $this->taskHandler = Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new ZombieUpdaterTask(), 20);
    }

    /**
     * @return EntitySizeInfo
     */
    protected function getInitialSizeInfo() : EntitySizeInfo {
        return new EntitySizeInfo(1.9, 0.6);
    }

    /**
     * @param CompoundTag $nbt
     * @return void
     */
    public function initEntity(CompoundTag $nbt) : void {
        parent::initEntity($nbt);

        $this->setMaxHealth(30);
        $this->setHealth(30);
    }

    /**
     * @param int $tickDiff
     * @return bool
     */
    public function entityBaseTick(int $tickDiff = 1) : bool {
        if($this->closed){
            return false;
        }
        if($this->session === null){
            $this->flagForDespawn();
            return false;
        }
        if(--$this->time === 0){
            if(SessionFactory::getInstance()->getOfflineSession(($name = $this->session->getName())) !== null){
                SessionFactory::getInstance()->removeOfflineSession($name);
            }
            $this->flagForDespawn();
            return false;
        }
        if(Server::getInstance()->getPlayerByRawUUID($this->session->getRawUUID()) instanceof Player){
            $this->flagForDespawn();
            return false;
        }
        $this->setNameTag(TextHelper::replace("&7(&cDISCONNECTED&7)&r &f{$this->session->getName()}"));

        return parent::entityBaseTick($tickDiff);
    }

    /**
     * @return void
     */
    protected function onDeath() : void {
        if(SessionFactory::getInstance()->getOfflineSession(($name = $this->session->getName())) !== null){
            SessionFactory::getInstance()->removeOfflineSession($name);
        }
        if(!ScenarioFactory::getInstance()->isActive(GameScenarios::TIME_BOMB)){
            $this->session->genGrave();
        }
        $this->startDeathAnimation();
    }

    /**
     * @param EntityDamageEvent $source
     * @return void
     */
    public function attack(EntityDamageEvent $source) : void {
        $player = $this->session;
        if(GameArena::getInstance()->getGamemodeType() === GamemodeType::TEAMS() && $source instanceof EntityDamageByEntityEvent){
            $attacker = $source->getDamager();
            if(!$attacker instanceof Player) return;

            $playerTeam = SessionFactory::getInstance()->getSession($player->getName());
            $attackerTeam = SessionFactory::getInstance()->getSession($attacker->getName());

            if($attackerTeam->getTeam() instanceof Team && $playerTeam->getTeam() instanceof Team && $attackerTeam->getTeam()->equals($playerTeam->getTeam())){
                $source->cancel();
            }
        }
    }

    /**
     * @param int $time
     * @return void
     */
    public function setDespawnTime(int $time = 600) : void {
        $this->time = $time * 8;
    }

    /**
     * @param Session $session
     * @return void
     */
    public function setSession(Session $session) : void {
        $this->session = SessionFactory::getInstance()->getOfflineSession($session->getName())->getOrigin();
    }
}

?>