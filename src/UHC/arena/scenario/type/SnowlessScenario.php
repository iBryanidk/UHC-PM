<?php

namespace UHC\arena\scenario\type;

use UHC\arena\scenario\Scenario;

use pocketmine\item\VanillaItems;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerInteractEvent;

class SnowlessScenario extends Scenario {

    /**
     * @return string
     */
    public function getName() : string {
        return self::SNOW_LESS;
    }

    /**
     * @return string
     */
    public function getDescription() : string {
        return "Snow balls cannot be used";
    }

    /**
     * @param CraftItemEvent $event
     * @return void
     */
    public function onCraftItemEvent(CraftItemEvent $event) : void {
        if(!$this->isActive()){
            return;
        }
        foreach($event->getOutputs() as $item){
            if($item->equals(VanillaItems::SNOWBALL())){
                $event->cancel();
            }
        }
    }

    /**
     * @param PlayerItemUseEvent $event
     * @return void
     */
    public function onPlayerItemUseEvent(PlayerItemUseEvent $event) : void {
        if(!$this->isActive()){
            return;
        }
        $player = $event->getPlayer();
        $item = $event->getItem();
        if($item->equals(VanillaItems::SNOWBALL())){
            $event->cancel();
            $player->getInventory()->setItemInHand(VanillaItems::AIR());
        }
    }

    /**
     * @param PlayerInteractEvent $event
     * @return void
     */
    public function onPlayerInteractEvent(PlayerInteractEvent $event) : void {
        if(!$this->isActive()){
            return;
        }
        $player = $event->getPlayer();
        $item = $event->getItem();
        if($item->equals(VanillaItems::SNOWBALL()) && $event->getAction() === $event::RIGHT_CLICK_BLOCK){
            $event->cancel();
            $player->getInventory()->setItemInHand(VanillaItems::AIR());
        }
    }
}

?>