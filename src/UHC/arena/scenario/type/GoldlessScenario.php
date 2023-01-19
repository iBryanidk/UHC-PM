<?php

namespace UHC\arena\scenario\type;

use UHC\arena\scenario\Scenario;

use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;

class GoldlessScenario extends Scenario {

    /**
     * @return string
     */
    public function getName() : string {
        return self::GOLD_LESS;
    }

    /**
     * @return string
     */
    public function getDescription() : string {
        return "Gold cannot be used";
    }

    /**
     * @param BlockBreakEvent $event
     * @return void
     */
    public function onBlockBreakEvent(BlockBreakEvent $event) : void {
        if(!$this->isActive()){
            return;
        }
        $block = $event->getBlock();
        if($block->getId() === (VanillaBlocks::DIAMOND_ORE())->getId()){
            $event->setDrops([]);
        }
    }
}

?>