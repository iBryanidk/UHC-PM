<?php

namespace UHC\world\inventory;

use UHC\session\SessionFactory;
use pocketmine\player\Player;

class EnchantInventory extends \pocketmine\block\inventory\EnchantInventory {

    /**
     * @param Player $who
     * @return void
     */
    public function onClose(Player $who) : void {
        parent::onClose($who);

        (SessionFactory::getInstance()->getSession($who->getName()))->setEnchantingTransaction();
    }
}

?>