<?php

namespace UHC\arena\scenario\type;

use UHC\arena\scenario\Scenario;

use pocketmine\event\entity\EntityDamageEvent;

class FirelessScenario extends Scenario {

    /**
     * @return string
     */
    public function getName() : string {
        return self::FIRE_LESS;
    }

    /**
     * @return string
     */
    public function getDescription() : string {
        return "Cannot take damage by fire";
    }

    /**
     * @param EntityDamageEvent $event
     * @return void
     */
    public function onEntityDamageEvent(EntityDamageEvent $event) : void {
        if(!$this->isActive()){
            return;
        }
        $player = $event->getEntity();
        switch($event->getCause()){
            case $event::CAUSE_FIRE:
            case $event::CAUSE_FIRE_TICK:
            case $event::CAUSE_LAVA:
                $event->cancel();
                $player->extinguish();
            break;
        }
    }
}

?>