<?php

namespace UHC\world\inventory\action;

use UHC\Loader;

use UHC\session\Session;
use UHC\session\SessionFactory;

use UHC\world\inventory\EnchantInventory;
use UHC\world\inventory\utils\NetworkInventoryAction;
use UHC\world\inventory\transaction\EnchantingTransaction;

use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\item\VanillaItems;
use pocketmine\plugin\PluginException;

use pocketmine\block\inventory\EnchantInventory as EnchantInventoryAlias;

use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\inventory\transaction\action\InventoryAction;

class EnchantingAction extends InventoryAction {

    /**
     * EnchantingAction Constructor.
     * @param EnchantInventory $inventory
     * @param int $inventorySlot
     * @param Item $sourceItem
     * @param Item $targetItem
     * @param int $type
     */
    public function __construct(
        protected EnchantInventory $inventory,
        protected int $inventorySlot,
        Item $sourceItem,
        Item $targetItem,
        protected int $type
    ){
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
        if($session->getEnchantingTransaction() === null){
            throw new PluginException("Player doesn't have an existing enchanting transaction");
        }
        if(!$this->inventory->slotExists($this->inventorySlot)){
            throw new PluginException("Slot does not exist");
        }
        if($this->sourceItem->equals($this->inventory->getItem(EnchantInventoryAlias::SLOT_INPUT), false, false) || $this->isBook($this->getTargetItem())){
            foreach($this->targetItem->getEnchantments() as $enchantment){
                if($enchantment->getLevel() > $enchantment->getType()->getMaxLevel()){
                    throw new PluginException("Enchantment level exceeds its max level");
                }
            }
        }
    }

    /**
     * @param Player $source
     * @return bool
     */
    public function onPreExecute(Player $source) : bool {
        return true;
    }

    /**
     * @param Item $targetItem
     * @return bool
     */
    public function isBook(Item $targetItem) : bool {
        return $targetItem->equals(VanillaItems::BOOK());
    }

    /**
     * @param InventoryTransaction $transaction
     * @return void
     */
    public function onAddToTransaction(InventoryTransaction $transaction) : void {
        $transaction->addInventory($this->inventory);
    }

    /**
     * @param Player $source
     * @return void
     */
    public function execute(Player $source) : void {
        if(!$this->inventory instanceof EnchantInventory){
            Loader::getInstance()->getLogger()->warning("Inventory not instanceof EnchantInventory");
            return;
        }
        if(!($session = SessionFactory::getInstance()->getSession($source->getName())) instanceof Session){
            throw new PluginException("Session of {$source->getName()} don't exists");
        }
        /** @var EnchantingTransaction $transaction */
        $transaction = $session->getEnchantingTransaction();
        switch($this->getType()) {
            case NetworkInventoryAction::SOURCE_TYPE_ENCHANT_MATERIAL:
                if(($cost = $this->getTargetItem()->getCount() - $this->getSourceItem()->getCount()) > $source->getXpManager()->getXpLevel()){
                    throw new PluginException("Player XP Level is lower than cost");
                }
                $transaction->setCost($cost);
                if(!($item = $this->inventory->getItem(EnchantInventoryAlias::SLOT_LAPIS))->isNull()){
                    $this->inventory->setItem(EnchantInventoryAlias::SLOT_LAPIS, ($item->getCount() <= 1 ? VanillaItems::AIR() : $item->setCount($item->getCount() - $transaction->getCost())));
                }
                $oldLevel = $source->getXpManager()->getXpLevel();
                if(!$source->isCreative()){
                    $source->getXpManager()->setXpLevel($oldLevel - $transaction->getCost());
                }
            break;
            case NetworkInventoryAction::SOURCE_TYPE_ENCHANT_OUTPUT:
                $transaction->onSuccess($this->inventory, $this->getSourceItem());
            break;
        }
    }
}

?>