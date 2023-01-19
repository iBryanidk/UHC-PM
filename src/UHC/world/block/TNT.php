<?php

namespace UHC\world\block;

use UHC\world\entities\PrimedTNT;

use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\player\Player;
use pocketmine\world\sound\IgniteSound;

use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Arrow;

use pocketmine\item\Item;
use pocketmine\item\Durable;
use pocketmine\item\FlintSteel;
use pocketmine\item\enchantment\VanillaEnchantments;

use pocketmine\block\Opaque;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\BlockLegacyMetadata;

class TNT extends Opaque {

    /** @var bool */
    protected bool $unstable = false;

    /** @var bool */
    protected bool $worksUnderwater = false;

    /**
     * @param int $id
     * @param int $stateMeta
     * @return void
     */
    public function readStateFromData(int $id, int $stateMeta) : void {
        $this->unstable = ($stateMeta & BlockLegacyMetadata::TNT_FLAG_UNSTABLE) !== 0;
        $this->worksUnderwater = ($stateMeta & BlockLegacyMetadata::TNT_FLAG_UNDERWATER) !== 0;
    }

    /**
     * @return int
     */
    protected function writeStateToMeta() : int {
        return ($this->unstable ? BlockLegacyMetadata::TNT_FLAG_UNSTABLE : 0) | ($this->worksUnderwater ? BlockLegacyMetadata::TNT_FLAG_UNDERWATER : 0);
    }

    /**
     * @return int
     */
    protected function writeStateToItemMeta() : int {
        return $this->worksUnderwater ? BlockLegacyMetadata::TNT_FLAG_UNDERWATER : 0;
    }

    /**
     * @return int
     */
    public function getStateBitmask() : int {
        return 0b11;
    }

    /**
     * @return bool
     */
    public function isUnstable() : bool{ return $this->unstable; }

    /**
     * @param bool $unstable
     * @return $this
     */
    public function setUnstable(bool $unstable) : self {
        $this->unstable = $unstable;
        return $this;
    }

    /**
     * @return bool
     */
    public function worksUnderwater() : bool { return $this->worksUnderwater; }

    /**
     * @param bool $worksUnderwater
     * @return $this
     */
    public function setWorksUnderwater(bool $worksUnderwater) : self {
        $this->worksUnderwater = $worksUnderwater;
        return $this;
    }

    /**
     * @param Item $item
     * @param Player|null $player
     * @return bool
     */
    public function onBreak(Item $item, ?Player $player = null) : bool {
        if($this->unstable){
            $this->ignite();
            return true;
        }
        return parent::onBreak($item, $player);
    }

    /**
     * @param Item $item
     * @param int $face
     * @param Vector3 $clickVector
     * @param Player|null $player
     * @return bool
     */
    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool {
        if($item instanceof FlintSteel || $item->hasEnchantment(VanillaEnchantments::FIRE_ASPECT())){
            if($item instanceof Durable){
                $item->applyDamage(1);
            }
            $this->ignite();
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function hasEntityCollision() : bool {
        return true;
    }

    /**
     * @param Entity $entity
     * @return bool
     */
    public function onEntityInside(Entity $entity) : bool {
        if($entity instanceof Arrow && $entity->isOnFire()){
            $this->ignite();
            return false;
        }
        return true;
    }

    /**
     * @param int $fuse
     * @return void
     */
    public function ignite(int $fuse = 80) : void {
        $this->position->getWorld()->setBlock($this->position, VanillaBlocks::AIR());

        $mot = (new Random())->nextSignedFloat() * M_PI * 2;

        $tnt = new PrimedTNT(Location::fromObject($this->position->add(0.5, 0, 0.5), $this->position->getWorld()));
        $tnt->setFuse($fuse);
        $tnt->setWorksUnderwater(false);
        $tnt->setMotion(new Vector3(-sin($mot) * 0.02, 0.2, -cos($mot) * 0.02));

        $tnt->spawnToAll();
        $tnt->broadcastSound(new IgniteSound());
    }

    /**
     * @return int
     */
    public function getFlameEncouragement() : int {
        return 15;
    }

    /**
     * @return int
     */
    public function getFlammability() : int {
        return 100;
    }

    /**
     * @return void
     */
    public function onIncinerate() : void {
        $this->ignite();
    }
}

?>