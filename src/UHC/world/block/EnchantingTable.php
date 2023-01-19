<?php

namespace UHC\world\block;

use UHC\world\inventory\EnchantInventory;

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class EnchantingTable extends \pocketmine\block\EnchantingTable {

    /**
     * @param Item $item
     * @param int $face
     * @param Vector3 $clickVector
     * @param Player|null $player
     * @return bool
     */
    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool {
        $player?->setCurrentWindow(new EnchantInventory($this->getPosition()));
        return true;
    }
}

?>