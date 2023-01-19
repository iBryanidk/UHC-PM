<?php

namespace UHC\item;

use UHC\world\entities\FishingHook;

use pocketmine\entity\projectile\Throwable;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\item\Durable;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\entity\Location;
use pocketmine\world\sound\ThrowSound;

class FishingRod extends Durable {

    /**
     * @param Location $location
     * @param Player $thrower
     * @return Throwable
     */
    protected function createEntity(Location $location, Player $thrower) : Throwable {
        return new FishingHook($location, $thrower);
    }

    /**
     * @return int
     */
    public function getCooldownTicks(): int {
        return 20;
    }

    /**
     * @return int
     */
    public function getMaxDurability() : int {
        return 300;
    }

    /**
     * @return float
     */
    public function getThrowForce() : float {
        return 1.4;
    }

    /**
     * @param Player $player
     * @param Vector3 $directionVector
     * @return ItemUseResult
     */
    public function onClickAir(Player $player, Vector3 $directionVector) : ItemUseResult {
        if($player->hasItemCooldown($this)){
            $player->resetItemCooldown($this, $this->getCooldownTicks());
        }
        $location = $player->getLocation();

        $projectile = $this->createEntity(Location::fromObject($player->getEyePos(), $player->getWorld(), $location->yaw, $location->pitch), $player);
        $projectile->setMotion($directionVector->multiply($this->getThrowForce()));

        $projectileEv = new ProjectileLaunchEvent($projectile);
        $projectileEv->call();
        if($projectileEv->isCancelled()){
            $projectile->flagForDespawn();
            return ItemUseResult::FAIL();
        }
        $projectile->spawnToAll();
        $location->getWorld()->addSound($location, new ThrowSound());

        $this->applyDamage(1);
        return ItemUseResult::SUCCESS();
    }

    /**
     * @return int
     */
    public function getMaxStackSize() : int {
        return 1;
    }
}

?>