<?php

namespace UHC\arena\scenario\type;

use UHC\arena\scenario\Scenario;

use pocketmine\block\VanillaBlocks;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageEvent;

class BloodDiamondScenario extends Scenario {

    /**
     * @return string
     */
    public function getName() : string {
        return self::BLOOD_DIAMOND;
    }

    /**
     * @return string
     */
    public function getDescription() : string {
        return "Chopping a diamond ore deals damage";
    }

    /**
     * @param BlockBreakEvent $event
     * @return void
     */
    public function onBlockBreakEvent(BlockBreakEvent $event) : void {
        if(!$this->isActive()){
            return;
        }
        $player = $event->getPlayer();
        $block = $event->getBlock();
        if($block->getId() === (VanillaBlocks::DIAMOND_ORE())->getId()){
            $player->attack(new EntityDamageEvent($player, EntityDamageEvent::CAUSE_MAGIC, 1));
        }
    }
}

?>