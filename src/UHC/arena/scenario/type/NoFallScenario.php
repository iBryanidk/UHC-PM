<?php

namespace UHC\arena\scenario\type;

use UHC\arena\scenario\Scenario;

use pocketmine\event\entity\EntityDamageEvent;

class NoFallScenario extends Scenario {

    /**
     * @return string
     */
    public function getName() : string {
        return self::NO_FALL;
    }

    /**
     * @return string
     */
    public function getDescription() : string {
        return "No fall damage";
    }

    /**
     * @param EntityDamageEvent $event
     * @return void
     */
    public function onEntityDamageEvent(EntityDamageEvent $event) : void {
        if(!$this->isActive()){
            return;
        }
        if($event->getCause() === $event::CAUSE_FALL){
            $event->cancel();
        }
    }
}

?>