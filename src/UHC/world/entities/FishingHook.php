<?php

namespace UHC\world\entities;

use pocketmine\entity\Location;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\entity\projectile\Throwable;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class FishingHook extends Throwable {

    public static function getNetworkTypeId() : string { return EntityIds::FISHING_HOOK; }

    /** @var float */
    protected float $height = 0.25;

    /** @var float */
    protected float $width = 0.25;

    /**
     * FishingHook Constructor.
     * @param Location $location
     * @param Entity|null $shootingEntity
     * @param CompoundTag|null $nbt
     */
    public function __construct(Location $location, ?Entity $shootingEntity, ?CompoundTag $nbt = null){
        parent::__construct($location, $shootingEntity, $nbt);
    }

    /**
     * @return EntitySizeInfo
     */
    protected function getInitialSizeInfo() : EntitySizeInfo {
        return new EntitySizeInfo($this->height, $this->width);
    }

    /**
     * @return float
     */
    protected function getInitialDragMultiplier() : float {
        return !isset($this->drag) ? 0.01 : $this->drag;
    }

    /**
     * @return float
     */
    protected function getInitialGravity() : float {
        return !isset($this->gravity) ? 0.03 : $this->gravity;
    }

    /** 
	 * @param Int $currentTick
	 * @return bool
	 */
	public function onUpdate(Int $currentTick) : bool {
        parent::onUpdate($currentTick);
		if($this->closed){
			return false;
        }
        $this->timings->startTiming();
		if($this->isCollided){
			$this->flagForDespawn();
		}
		$this->timings->stopTiming();
        return true;
    }
}

?>