<?php

namespace UHC\arena\scenario\type;

use UHC\world\entities\PrimedTNT;
use UHC\arena\scenario\Scenario;

use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\entity\Location;

use pocketmine\world\sound\IgniteSound;
use pocketmine\block\VanillaBlocks;

use pocketmine\event\block\BlockBreakEvent;

class DiamondBombScenario extends Scenario {

    /**
     * @return string
     */
    public function getName() : string {
        return self::DIAMOND_BOMB;
    }

    /**
     * @return string
     */
    public function getDescription() : string {
        return "When chopping a diamond spawn a TNT";
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
            $block->getPosition()->getWorld()->setBlock($block->getPosition(), VanillaBlocks::AIR());

            $mot = (new Random())->nextSignedFloat() * M_PI * 2;

            $tnt = new PrimedTNT(Location::fromObject($block->getPosition()->add(0.5, 0, 0.5), $block->getPosition()->getWorld()));
            $tnt->setFuse(120);
            $tnt->setWorksUnderwater(false);
            $tnt->setMotion(new Vector3(-sin($mot) * 0.02, 0.2, -cos($mot) * 0.02));

            $tnt->spawnToAll();
            $tnt->broadcastSound(new IgniteSound());
        }
    }
}

?>