<?php

namespace UHC\world\inventory\handler;

use UHC\session\Session;

use UHC\world\inventory\EnchantInventory;
use UHC\world\inventory\action\EnchantingAction;
use UHC\world\inventory\transaction\EnchantingTransaction;
use UHC\world\inventory\transaction\utils\TransactionHandler;
use UHC\world\inventory\utils\NetworkInventoryAction as NetworkInventoryActionAlias;

use pocketmine\plugin\PluginException;

use pocketmine\network\mcpe\InventoryManager;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\ServerboundPacket;
use pocketmine\inventory\transaction\action\InventoryAction;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UIInventorySlotOffset;
use pocketmine\network\mcpe\protocol\types\inventory\NetworkInventoryAction;

class InventoryTransactionHandler extends TransactionHandler {

    /** @var Session */
    protected Session $session;

    public function __construct(){
        parent::__construct(InventoryTransactionPacket::class);
    }

    /**
     * @param Session $session
     * @param ServerboundPacket $packet
     * @return void
     */
    public function handle(Session $session, ServerboundPacket $packet) : void {
        $this->session = $session;
        if(!$packet instanceof InventoryTransactionPacket){
            return;
        }
        if($session->getPlayerNonNull()->getCurrentWindow() === null){
            return;
        }
        $actions = [];

        $anvil = false;
        $enchanting = false;

        foreach($packet->trData->getActions() as $action){
            if($this->isFromEnchantingTable($action)){
                $enchanting = true;
            }else{
                throw new PluginException("Only enchantment tables should be processed");
            }
            if(($action = $this->createInventoryAction($action)) !== null){
                $actions[] = $action;
            }
        }
        if($enchanting){
            $this->handleEnchanting($actions);
        }
    }

    /**
     * @param NetworkInventoryAction $action
     * @return bool
     */
    protected function isFromEnchantingTable(NetworkInventoryAction $action): bool {
        return ($action->sourceType === NetworkInventoryActionAlias::SOURCE_TODO && ($action->windowId === NetworkInventoryActionAlias::SOURCE_TYPE_ENCHANT_MATERIAL || $action->windowId === NetworkInventoryActionAlias::SOURCE_TYPE_ENCHANT_INPUT||$action->windowId === NetworkInventoryAction::SOURCE_TYPE_ENCHANT_OUTPUT))||($this->session->getEnchantingTransaction() !== null && !$action->oldItem->getItemStack()->equals($action->newItem->getItemStack()) && isset(UIInventorySlotOffset::ENCHANTING_TABLE[$action->inventorySlot])) || $this->session->getPlayerNonNull()->getCurrentWindow() instanceof EnchantInventory;
    }

    /**
     * @param InventoryAction[] $actions
     * @return void
     */
    protected function handleEnchanting(array $actions) : void {
        $player = $this->session->getPlayerNonNull();

        $enchantingTransaction = $this->session->getEnchantingTransaction();
        if($enchantingTransaction === null){
            $this->session->setEnchantingTransaction(($enchantingTransaction = new EnchantingTransaction($player, $actions)));
        }else{
            foreach($actions as $action){
                $enchantingTransaction->addAction($action);
            }
        }
        try {
            $enchantingTransaction->validate();
        } catch(PluginException){
            return;
        }
        $inventoryManager = $player->getNetworkSession()->getInvManager();
        if($inventoryManager === null){
            $this->session->setEnchantingTransaction();
            return;
        }
        try {
            $inventoryManager->onTransactionStart($enchantingTransaction);
            $enchantingTransaction->execute();
        } catch(\Exception){
            $this->sync($inventoryManager);
        } finally {
            $this->session->setEnchantingTransaction();
        }
    }


    /**
     * @param NetworkInventoryAction $action
     * @return InventoryAction|null
     */
    protected function createInventoryAction(NetworkInventoryAction $action) : ?InventoryAction {
        $player = $this->session->getPlayerNonNull();
        switch($action->sourceType){
            case NetworkInventoryAction::SOURCE_CONTAINER:
                $invManager = $player->getNetworkSession()->getInvManager();
                if($invManager === null){
                    return null;
                }
                return TypeConverter::getInstance()->createInventoryAction($action, $player, $invManager);
            case NetworkInventoryAction::SOURCE_TODO:
                $oldItem = TypeConverter::getInstance()->netItemStackToCore($action->oldItem->getItemStack());
                $newItem = TypeConverter::getInstance()->netItemStackToCore($action->newItem->getItemStack());

                $slot = UIInventorySlotOffset::ENCHANTING_TABLE[$action->inventorySlot] ?? UIInventorySlotOffset::ANVIL[$action->inventorySlot] ?? $action->inventorySlot;

                $currentInventory = $player->getCurrentWindow();
                return match($action->windowId){
                    NetworkInventoryActionAlias::SOURCE_TYPE_ENCHANT_INPUT,
                    NetworkInventoryActionAlias::SOURCE_TYPE_ENCHANT_MATERIAL,
                    NetworkInventoryActionAlias::SOURCE_TYPE_ENCHANT_OUTPUT => $currentInventory instanceof EnchantInventory ? new EnchantingAction($currentInventory, $slot, $oldItem, $newItem, $action->windowId) : null,
                    default => null,
                };
        }
        return null;
    }

    /**
     * @param InventoryManager $inventoryManager
     * @return void
     */
    public function sync(InventoryManager $inventoryManager) : void {
        $inventoryManager->syncAll();
    }
}

?>