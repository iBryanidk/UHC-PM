<?php

namespace UHC\arena\scenario\type;

use pocketmine\block\Block;
use pocketmine\block\Log;
use pocketmine\block\VanillaBlocks;
use UHC\arena\scenario\Scenario;

use pocketmine\event\block\BlockBreakEvent;

class TimberScenario extends Scenario {

    /**
     * @return string
     */
    public function getName() : string {
        return self::TIMBER;
    }

    /**
     * @return string
     */
    public function getDescription() : string {
        return "Trees cut themselves down";
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
        if($block instanceof Log){
            $this->treeCutter($block);
            $event->setDrops([]);
        }
    }

    /**
     * @param Block $blockClicked
     * @return void
     */
    protected function treeCutter(Block $blockClicked) : void {
        foreach($blockClicked->getAllSides() as $side){
            if(in_array($side->getId(), [VanillaBlocks::OAK_LOG()->getId(), VanillaBlocks::ACACIA_LOG()->getId(), VanillaBlocks::BIRCH_LOG()->getId(), VanillaBlocks::DARK_OAK_LOG()->getId(), VanillaBlocks::JUNGLE_LOG()->getId(), VanillaBlocks::SPRUCE_LOG()->getId()])){
                $blockClicked->getPosition()->getWorld()->useBreakOn($side->getPosition());

                $this->treeCutter($side);
            }
        }
    }
}

?>