<?php

namespace UHC\world\inventory\action;

use UHC\session\Session;
use UHC\session\SessionFactory;

use UHC\world\inventory\AnvilInventory;
use UHC\world\inventory\transaction\AnvilTransaction;
use UHC\world\inventory\utils\NetworkInventoryAction;

use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\plugin\PluginException;

use pocketmine\inventory\transaction\action\InventoryAction;

class AnvilAction extends InventoryAction {

    /**
     * AnvilAction Constructor.
     * @param AnvilInventory $inventory
     * @param int $inventorySlot
     * @param Item $sourceItem
     * @param Item $targetItem
     * @param int $type
     */
    public function __construct(
        protected AnvilInventory $inventory,
        protected int $inventorySlot,
        Item $sourceItem,
        Item $targetItem,
        protected int $type)
    {
        parent::__construct($sourceItem, $targetItem);
    }

    /**
     * @return int
     */
    public function getType(): int {
        return $this->type;
    }

    /**
     * @param Player $source
     * @return void
     */
    public function validate(Player $source) : void {
        if(!($session = SessionFactory::getInstance()->getSession($source->getName())) instanceof Session){
            throw new PluginException("Session of {$source->getName()} don't exists");
        }
        if($session->getAnvilTransaction() === null){
            throw new PluginException("Player doesn't have an existing enchanting transaction");
        }
        if(!$this->inventory->slotExists($this->inventorySlot)){
            throw new PluginException("Slot does not exist");
        }
        /** @var AnvilTransaction $transaction */
        $transaction = $session->getAnvilTransaction();
        switch($this->getType()){
            case NetworkInventoryAction::SOURCE_TYPE_ANVIL_RESULT:
                $transaction->setResult($this->getSourceItem());
            break;
        }
    }

    /**
     * @param Player $source
     * @return void
     */
    public function execute(Player $source) : void {
        if(!($session = SessionFactory::getInstance()->getSession($source->getName())) instanceof Session){
            throw new PluginException("Session of {$source->getName()} don't exists");
        }
        if($session->getAnvilTransaction() === null){
            throw new PluginException("Player doesn't have an existing enchanting transaction");
        }
        /** @var AnvilTransaction $transaction */
        $transaction = $session->getAnvilTransaction();
        switch($this->getType()){
            case NetworkInventoryAction::SOURCE_TYPE_ANVIL_RESULT:
                $transaction->onSuccess($this->inventory);
            break;
        }
    }
}

?>