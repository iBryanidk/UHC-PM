<?php

namespace UHC\arena\scenario\type;

use pocketmine\scheduler\ClosureTask;
use UHC\arena\scenario\Scenario;

use pocketmine\item\Axe;
use pocketmine\item\Pickaxe;
use pocketmine\item\Shovel;

use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use UHC\Loader;

class HasteyBoysScenario extends Scenario {

    /**
     * @return string
     */
    public function getName() : string {
        return self::HASTEY_BOYS;
    }

    /**
     * @return string
     */
    public function getDescription() : string {
        return "All tools crafted will be enchanted with Efficiency III and Unbreaking III";
    }

    /**
     * @param CraftItemEvent $event
     * @return void
     */
    public function onCraftItemEvent(CraftItemEvent $event) : void {
        if(!$this->isActive()){
            return;
        }
        $player = $event->getPlayer();
        foreach($event->getOutputs() as $oldItem){
            if($oldItem instanceof Pickaxe || $oldItem instanceof Axe || $oldItem instanceof Shovel){
                $newItem = clone $oldItem;

                $newItem->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 3));
                $newItem->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));

                Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($player, $oldItem, $newItem) : void {
                    $inventory = $player->getCursorInventory();
                    foreach(($realInventory = $player->getInventory())->getContents() as $slot => $inventoryItem){
                        if($inventoryItem->equals($oldItem)){
                            $index = $slot;
                            $inventory = $realInventory;
                        }
                    }
                    if(isset($index)){
                        $inventory->setItem($index, $newItem);
                    }else{
                        $inventory->setItem(0, $newItem);
                    }
                }), 1);
            }
        }
    }
}

?>