<?php

namespace UHC\arena\game\utils;

use pocketmine\block\utils\DyeColor;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\world\Position;

use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;

use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;

class GameBorder {

    /** @var int */
    const DEFAULT_BORDER_SIZE = 1000;

    /** @var int */
    protected int $border = self::DEFAULT_BORDER_SIZE;

    /** @var float */
    protected float $requiredPosition = 0.10 ** 2;

    /**
     * @return int
     */
    public function getBorder() : int {
        return $this->border;
    }

    /**
     * @param int $border
     * @return void
     */
    public function setBorder(int $border) : void {
        $this->border = $border;
    }

    /**
     * @return float
     */
    public function getRequiredPosition() : float {
        return $this->requiredPosition;
    }

    /**
     * @param Position $firstPosition
     * @param Position $secondPosition
     * @return int
     */
    public function getDistance(Position $firstPosition, Position $secondPosition) : int {
        return round(sqrt((($firstPosition->getFloorX() - $secondPosition->getFloorX()) ** 2) + (($firstPosition->getFloorZ() - $secondPosition->getFloorZ()) ** 2)));
    }

    /**
     * @param Player $player
     * @return void
     */
    public function buildWall(Player $player) : void {
        foreach($this->getNearbyBlocks($player->getPosition()) as $nearbyBlock){
            $this->buildBlock($player, Position::fromObject($nearbyBlock->subtract(2, 0, 2), $player->getWorld()), VanillaBlocks::BEDROCK());
        }
    }

    /**
     * @param Position $position
     * @return bool
     */
    public function isBorderLimit(Position $position) : bool {
        $border = $this->getBorder();

        return $position->getFloorX() >= -$border && $position->getFloorX() <= $border && $position->getFloorZ() >= -$border && $position->getFloorZ() <= $border;
    }

    /**
     * @param Position $position
     * @return Position
     */
    public function correctPosition(Position $position) : Position {
        $border = $this->getBorder();

        $x = $position->getFloorX();
        $y = $position->getFloorY();
        $z = $position->getFloorZ();

        $xMin = -$border;
        $xMax = $border;

        $zMin = -$border;
        $zMax = $border;

        if($x <= $xMin){
            $x = $xMin + 2;
        }elseif($x >= $xMax){
            $x = $xMax - 2;
        }
        if($z <= $zMin){
            $z = $zMin + 2;
        }elseif($z >= $zMax){
            $z = $zMax - 2;
        }
        return new Position($x, $y + 5, $z, $position->getWorld());
    }

    /**
     * @param Position $position
     * @return \Generator
     */
    public function getNearbyBlocks(Position $position) : \Generator {
        $radius = 4;
        $pos = $position->floor();

        for($y = -$radius; $y <= $radius; $y++){
            for($x = -$radius; $x <= $radius; $x++){
                for($z = -$radius; $z <= $radius; $z++){
                    if($this->isBorderLimit($cPos = Position::fromObject($pos->add($x, $y, $z), $position->getWorld()))){
                        continue;
                    }
                    yield $cPos;
                }
            }
        }
    }

    /**
     * @param Player $player
     * @param Position $position
     * @param Block|null $block
     * @return void
     */
    protected function buildBlock(Player $player, Position $position, ?Block $block = null) : void {
        $player->getNetworkSession()->sendDataPacket(UpdateBlockPacket::create(new BlockPosition($position->x, $position->y, $position->z), RuntimeBlockMapping::getInstance()->toRuntimeId($block !== null ? $block->getFullId() : VanillaBlocks::AIR()->getFullId()), UpdateBlockPacket::FLAG_NONE, UpdateBlockPacket::DATA_LAYER_NORMAL));
    }
}

?>