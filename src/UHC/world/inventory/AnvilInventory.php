<?php

namespace UHC\world\inventory;

use UHC\session\SessionFactory;
use pocketmine\player\Player;

class AnvilInventory extends \pocketmine\block\inventory\AnvilInventory {

    /** @var int */
    public const TARGET = 0;
    /** @var int */
    public const SACRIFICE = 1;

    /**
     * @param Player $who
     * @return void
     */
    public function onClose(Player $who): void {
        parent::onClose($who);

        (SessionFactory::getInstance()->getSession($who->getName()))->setAnvilTransaction();
    }
}

?>