<?php

namespace UHC\arena\scenario\type;

use UHC\Loader;
use UHC\utils\Time;
use UHC\utils\TextHelper;

use UHC\arena\game\GameArena;
use UHC\arena\game\utils\GameStatus;

use UHC\arena\scenario\Scenario;

use pocketmine\item\Item;
use pocketmine\scheduler\Task;
use pocketmine\event\player\PlayerDeathEvent;

use pocketmine\block\Air;
use pocketmine\block\Chest;
use pocketmine\block\tile\Chest as TileChest;
use pocketmine\block\VanillaBlocks;

use pocketmine\world\Position;
use pocketmine\world\Explosion;
use pocketmine\world\particle\FloatingTextParticle;

class TimeBombScenario extends Scenario {

    /**
     * @return string
     */
    public function getName() : string {
        return self::TIME_BOMB;
    }

    /**
     * @return string
     */
    public function getDescription() : string {
        return "Whe player dies leaves an explosive chest";
    }

    /**
     * @param PlayerDeathEvent $event
     * @return void
     */
    public function onPlayerDeathEvent(PlayerDeathEvent $event) : void {
        if(!$this->isActive()){
            return;
        }
        $event->setDrops([]);
        $player = $event->getPlayer();
        /** @var Item[] $contents */
        $contents = [];
        foreach($player->getInventory()->getContents() as $item){
            $contents[] = $item;
        }
        foreach($player->getArmorInventory()->getContents() as $item){
            $contents[] = $item;
        }
        $contents[] = Item::jsonDeserialize(Loader::getInstance()->getRecipes()->get("recipe")[0]["output"][0]);

        $player->getPosition()->getWorld()->setBlock($pos = $player->getPosition()->floor(), VanillaBlocks::CHEST()->setFacing(4));
        $firstTile = $player->getWorld()->getTile($pos);

        $player->getPosition()->getWorld()->setBlock($pos = $player->getPosition()->subtract(0, 0, $player->getPosition()->getFloorY() < 0 ? 1 : -1)->floor(), VanillaBlocks::CHEST()->setFacing(4));
        $secondTile = $player->getWorld()->getTile($pos);
        if($firstTile instanceof TileChest && $secondTile instanceof TileChest){
            foreach($firstTile->getBlock()->getAllSides() as $side){
                if(!$side instanceof Air && !$side instanceof Chest){
                    $side->getPosition()->getWorld()->setBlock($side->getPosition(), VanillaBlocks::AIR());
                }
            }
            foreach($secondTile->getBlock()->getAllSides() as $side){
                if(!$side instanceof Air && !$side instanceof Chest){
                    $side->getPosition()->getWorld()->setBlock($side->getPosition(), VanillaBlocks::AIR());
                }
            }
            $firstTile->pairWith($secondTile);
            $secondTile->pairWith($firstTile);

            $firstTile->getInventory()->setContents($contents);
        }
        Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new class($player->getName(), Position::fromObject($player->getPosition()->floor(), $player->getWorld())) extends Task {

            /** @var int */
            protected int $time = 30;

            /** @var FloatingTextParticle */
            protected FloatingTextParticle $floatingTextParticle;

            /**
             * @param string $name
             * @param Position|null $position
             */
            public function __construct(
                protected string $name,
                protected ?Position $position = null,
            ){
                $this->floatingTextParticle = new FloatingTextParticle(TextHelper::replace(Time::getTimeToFullString($this->time)), TextHelper::replace("&r&a{$this->name}&f corpse will be explode in: "));
            }

            /**
             * @return void
             */
            public function onRun() : void {
                if(--$this->time === 0){
                    $this->explode();
                    $this->remove();

                    GameArena::getInstance()->broadcastMessage(TextHelper::replace("&a{$this->name}'s&r corpse has exploded"));

                    $this->getHandler()->cancel();
                }else{
                    if(GameArena::getInstance()->getStatus() !== GameStatus::RUNNING){
                        $this->explode();
                        $this->remove();
                        $this->getHandler()->cancel();
                    }
                    $this->update();
                }
            }

            /**
             * @return void
             */
            protected function update() : void {
                $this->floatingTextParticle->setText(TextHelper::replace(Time::getTimeToFullString($this->time)));
                $this->position->getWorld()->addParticle($this->position->add(0.9, 1, 0.9), $this->floatingTextParticle);
            }

            /**
             * @return void
             */
            protected function remove() : void {
                $this->floatingTextParticle->setInvisible();
                $this->position->getWorld()->addParticle($this->position->add(0.9, 1, 0.9), $this->floatingTextParticle);
            }

            /**
             * @return void
             */
            protected function explode() : void {
                if($this->position === null){
                    return;
                }
                $tile = $this->position->getWorld()->getTile($this->position);
                if($tile instanceof TileChest){
                    $tile->getInventory()->clearAll();
                }
                $explosion = new Explosion($this->position, 5);
                $explosion->explodeA();
                $explosion->explodeB();
            }
        }, 20);
    }
}

?>