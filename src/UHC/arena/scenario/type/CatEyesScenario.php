<?php

namespace UHC\arena\scenario\type;

use UHC\arena\game\GameArena;
use UHC\arena\scenario\Scenario;

use UHC\event\GameStartEvent;
use UHC\event\GamePlayerJoinEvent;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;

class CatEyesScenario extends Scenario {

    /**
     * @return string
     */
    public function getName() : string {
        return self::CAT_EYES;
    }

    /**
     * @return string
     */
    public function getDescription() : string {
        return "All have night vision";
    }

    /**
     * @param GameStartEvent $event
     * @return void
     */
    public function onGameStartEvent(GameStartEvent $event) : void {
        if(!$this->isActive()){
            return;
        }
        foreach(GameArena::getInstance()->getWorld()->getPlayers() as $player){
            $effect = $player->getEffects();
            $effect->add(new EffectInstance(VanillaEffects::NIGHT_VISION(), 2147483647, 1));
        }
    }

    /**
     * @param GamePlayerJoinEvent $event
     * @return void
     */
    public function onGamePlayerJoinEvent(GamePlayerJoinEvent $event) : void {
        if(!$this->isActive()){
            return;
        }
        $player = $event->getPlayer();
        $effect = $player->getEffects();
        $effect->add(new EffectInstance(VanillaEffects::NIGHT_VISION(), 2147483647, 1));
    }
}

?>