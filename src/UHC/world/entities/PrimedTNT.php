<?php

namespace UHC\world\entities;

use UHC\world\Explosion;

use pocketmine\math\Vector3;
use pocketmine\world\Position;

use pocketmine\entity\Entity;
use pocketmine\entity\Explosive;
use pocketmine\entity\EntitySizeInfo;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ExplosionPrimeEvent;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;

class PrimedTNT extends Entity implements Explosive {

    public static function getNetworkTypeId() : string { return EntityIds::TNT; }

    /** @var float */
    protected $gravity = 0.04;
    /** @var float */
    protected $drag = 0.02;

    /** @var int */
    protected int $fuse;

    /** @var bool */
    protected bool $worksUnderwater = false;

    /** @var bool */
    public $canCollide = false;

    /**
     * @return EntitySizeInfo
     */
    protected function getInitialSizeInfo() : EntitySizeInfo {
        return new EntitySizeInfo(0.98, 0.98);
    }

    /**
     * @return int
     */
    public function getFuse() : int {
        return $this->fuse;
    }

    /**
     * @param int $fuse
     * @return void
     */
    public function setFuse(int $fuse) : void {
        if($fuse < 0 || $fuse > 32767){
            throw new \InvalidArgumentException("Fuse must be in the range 0-32767");
        }
        $this->fuse = $fuse;
        $this->networkPropertiesDirty = true;
    }

    /**
     * @return bool
     */
    public function worksUnderwater() : bool { return $this->worksUnderwater; }

    /**
     * @param bool $worksUnderwater
     * @return void
     */
    public function setWorksUnderwater(bool $worksUnderwater) : void {
        $this->worksUnderwater = $worksUnderwater;
        $this->networkPropertiesDirty = true;
    }

    /**
     * @param EntityDamageEvent $source
     * @return void
     */
    public function attack(EntityDamageEvent $source) : void {
        if($source->getCause() === EntityDamageEvent::CAUSE_VOID){
            parent::attack($source);
        }
    }

    /**
     * @param CompoundTag $nbt
     * @return void
     */
    protected function initEntity(CompoundTag $nbt) : void {
        parent::initEntity($nbt);

        $this->fuse = $nbt->getShort("Fuse", 80);
    }

    /**
     * @param Entity $entity
     * @return bool
     */
    public function canCollideWith(Entity $entity) : bool {
        return false;
    }

    /**
     * @return CompoundTag
     */
    public function saveNBT() : CompoundTag {
        $nbt = parent::saveNBT();
        $nbt->setShort("Fuse", $this->fuse);

        return $nbt;
    }

    /**
     * @param int $tickDiff
     * @return bool
     */
    protected function entityBaseTick(int $tickDiff = 1) : bool {
        if($this->closed){
            return false;
        }
        $hasUpdate = parent::entityBaseTick($tickDiff);
        if(!$this->isFlaggedForDespawn()){
            $this->fuse -= $tickDiff;
            $this->networkPropertiesDirty = true;

            if($this->fuse <= 0){
                $this->flagForDespawn();
                $this->explode();
            }
        }
        return $hasUpdate || $this->fuse >= 0;
    }

    /**
     * @return void
     */
    public function explode() : void {
        $ev = new ExplosionPrimeEvent($this, 4);
        $ev->call();
        if(!$ev->isCancelled()){
            $explosion = new Explosion(Position::fromObject($this->location->add(0, $this->size->getHeight() / 2, 0), $this->getWorld()), $ev->getForce(), $this);
            if($ev->isBlockBreaking()){
                $explosion->explodeA();
            }
            $explosion->explodeB();
        }
    }

    /**
     * @param EntityMetadataCollection $properties
     * @return void
     */
    protected function syncNetworkData(EntityMetadataCollection $properties) : void {
        parent::syncNetworkData($properties);

        $properties->setGenericFlag(EntityMetadataFlags::IGNITED, true);
        $properties->setInt(EntityMetadataProperties::VARIANT, $this->worksUnderwater ? 1 : 0);
        $properties->setInt(EntityMetadataProperties::FUSE_LENGTH, $this->fuse);
    }

    /**
     * @param Vector3 $vector3
     * @return Vector3
     */
    public function getOffsetPosition(Vector3 $vector3) : Vector3 {
        return $vector3->add(0, 0.49, 0);
    }
}

?>