<?php

namespace UHC\world\block;

use UHC\world\inventory\AnvilInventory;

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

use pocketmine\block\VanillaBlocks;

use pocketmine\world\sound\AnvilUseSound;
use pocketmine\world\sound\AnvilBreakSound;

class Anvil extends \pocketmine\block\Anvil {

    const DAMAGE_CHANCE = 12;

    /**
     * @param Item $item
     * @param int $face
     * @param Vector3 $clickVector
     * @param Player|null $player
     * @return bool
     */
    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool {
        $player?->setCurrentWindow(new AnvilInventory($this->getPosition()));
        return true;
    }

    /**
     * @return void
     */
    public function use() : void {

        ($position = $this->getPosition())->getWorld()->addSound($position, new AnvilUseSound());

        if(mt_rand(0, 100) <= self::DAMAGE_CHANCE){

            $damage = $this->getDamage();

            if(++$damage > 2){
                $position->getWorld()->setBlock($position, VanillaBlocks::AIR());
                $position->getWorld()->addSound($position, new AnvilBreakSound());
                return;
            }
            $this->setDamage($damage);
        }
    }
}

?>