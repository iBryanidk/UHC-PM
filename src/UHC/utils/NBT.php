<?php

namespace UHC\utils;

use pocketmine\math\Vector3;
use pocketmine\nbt\tag\{FloatTag, CompoundTag, DoubleTag, ListTag};

final class NBT {

    /**
     * @param float $x
     * @param float $y
     * @param float $z
     * @param Vector3|null $motion
     * @param float $yaw
     * @param float $pitch
     * @return CompoundTag
     */
    public static function createBaseNBT(float $x, float $y, float $z, Vector3 $motion = null, float $yaw = 0.0, float $pitch = 0.0) : CompoundTag {
        return CompoundTag::create()
            ->setTag("Pos", new ListTag([
                new DoubleTag($x),
                new DoubleTag($y),
                new DoubleTag($z),
            ]))
            ->setTag("Motion", new ListTag([
                new DoubleTag($motion->x ?? 0.0),
                new DoubleTag($motion->y ?? 0.0),
                new DoubleTag($motion->z ?? 0.0)
            ]))
            ->setTag("Rotation", new ListTag([
                new FloatTag($yaw),
                new FloatTag($pitch),
            ]));
    }

    /**
     * @param Vector3 $position
     * @param Vector3|null $motion
     * @param float $yaw
     * @param float $pitch
     * @return CompoundTag
     */
    public static function createEntityNBT(Vector3 $position, Vector3 $motion = null, float $yaw = 0.0, float $pitch = 0.0) : CompoundTag {
        return CompoundTag::create()
            ->setTag("Pos", new ListTag([
                new DoubleTag($position->x),
                new DoubleTag($position->y),
                new DoubleTag($position->z),
            ]))
            ->setTag("Motion", new ListTag([
                new DoubleTag($motion->x ?? 0.0),
                new DoubleTag($motion->y ?? 0.0),
                new DoubleTag($motion->z ?? 0.0)
            ]))
            ->setTag("Rotation", new ListTag([
                new FloatTag($yaw),
                new FloatTag($pitch),
            ]));
    }
}

?>