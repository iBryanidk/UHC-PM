<?php

namespace UHC\arena\scenario\type;

use UHC\arena\scenario\Scenario;

use pocketmine\item\VanillaItems;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;

class CutCleanScenario extends Scenario {

    /**
     * @return string
     */
    public function getName() : string {
        return self::CUT_CLEAN;
    }

    /**
     * @return string
     */
    public function getDescription() : string {
        return "Minerals when mined become ingots";
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
        $drops = $event->getDrops();
        switch($block->getId()){
            case (VanillaBlocks::IRON_ORE())->getId():
                $drops = [VanillaItems::IRON_INGOT()];
                $event->setXpDropAmount(mt_rand(2, 5));
            break;
            case (VanillaBlocks::GOLD_ORE())->getId():
                $drops = [VanillaItems::GOLD_INGOT()];
                $event->setXpDropAmount(mt_rand(2, 5));
            break;
        }
        $event->setDrops($drops);
    }
}

?>