<?php

namespace UHC\world\inventory\transaction;

use UHC\world\inventory\action\EnchantingAction;

use pocketmine\plugin\PluginException;

use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\inventory\transaction\action\InventoryAction;

class EnchantingTransaction extends InventoryTransaction {

    /** @var int */
    protected int $cost = 1;

    /**
     * @return int
     */
    public function getCost() : int {
        return $this->cost;
    }

    /**
     * @param int $cost
     * @return void
     */
    public function setCost(int $cost) : void {
        $this->cost = $cost;
    }

    /**
     * @param InventoryAction $action
     * @return void
     */
    public function addAction(InventoryAction $action) : void {
        if(!$action instanceof EnchantingAction){
            return;
        }
        parent::addAction($action);
    }

    /**
     * @return void
     */
    public function validate() : void {
        $this->squashDuplicateSlotChanges();
        if(count($this->actions) < 3){
            throw new PluginException("Transaction must have at least three actions to be executable");
        }
        foreach($this->actions as $action){
            $action->validate($this->getSource());
        }
    }
}

?>