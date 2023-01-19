<?php

namespace UHC\listener;

use UHC\utils\TextHelper;
use UHC\arena\team\TeamFactory;

use UHC\session\Session;
use UHC\session\SessionFactory;

use UHC\arena\scenario\GameScenarios;
use UHC\arena\scenario\ScenarioFactory;

use UHC\world\inventory\EnchantInventory;
use UHC\world\inventory\transaction\InventoryFactory;

use UHC\arena\game\GameArena;
use UHC\arena\game\GameSettings;

use UHC\arena\game\utils\GameStatus;
use UHC\arena\game\utils\GamemodeType;

use UHC\arena\scenario\utils\GameSettingsForm;

use UHC\event\GamePlayerJoinEvent;
use UHC\event\GamePlayerQuitEvent;

use pocketmine\Server;
use pocketmine\event\Listener;

use pocketmine\player\Player;
use pocketmine\player\GameMode;

use pocketmine\item\GoldenApple;
use pocketmine\item\VanillaItems;
use pocketmine\block\VanillaBlocks;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;

use pocketmine\block\BlockLegacyIds;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;

use pocketmine\event\player\PlayerItemHeldEvent;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerExhaustEvent;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\event\server\DataPacketReceiveEvent;

final class GameListener implements Listener {

    /**
     * @param PlayerJoinEvent $event
     * @return void
     */
    public function onPlayerJoinEvent(PlayerJoinEvent $event) : void {
        $player = $event->getPlayer();

        $player->getEffects()->clear();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();

        $player->setGamemode(GameMode::SURVIVAL());

        $player->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
    }

    /**
     * @param GamePlayerJoinEvent $event
     * @return void
     */
    public function onGamePlayerJoinEvent(GamePlayerJoinEvent $event) : void {
        $player = $event->getPlayer();

        $player->getEffects()->clear();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();

        $player->setGamemode(GameMode::SURVIVAL());

        $player->getXpManager()->setXpAndProgress(0, 0.0);

        $player->getInventory()->addItem(VanillaItems::LEATHER()->setCount(64));
        $player->getInventory()->addItem(VanillaItems::STEAK()->setCount(64));

        $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
        $player->getHungerManager()->setSaturation($player->getHungerManager()->getMaxFood());
    }

    /**
     * @param GamePlayerQuitEvent $event
     * @return void
     */
    public function onGamePlayerQuitEvent(GamePlayerQuitEvent $event) : void {}

    /**
     * @param BlockPlaceEvent $event
     * @return void
     */
    public function onBlockPlaceEvent(BlockPlaceEvent $event) : void {
        $player = $event->getPlayer();
        if(GameArena::getInstance()->getStatus() !== GameStatus::RUNNING && !Server::getInstance()->isOp($player->getName())){
            $event->cancel();
        }
    }

    /**
     * @param BlockBreakEvent $event
     * @return void
     */
    public function onBlockBreakEvent(BlockBreakEvent $event) : void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $item = $event->getItem();
        if(GameArena::getInstance()->getStatus() !== GameStatus::RUNNING && !Server::getInstance()->isOp($player->getName())){
            $event->cancel();
            return;
        }
        switch($block){
            case VanillaBlocks::ACACIA_LEAVES():
            case VanillaBlocks::BIRCH_LEAVES():
            case VanillaBlocks::DARK_OAK_LEAVES():
            case VanillaBlocks::JUNGLE_LEAVES():
            case VanillaBlocks::OAK_LEAVES():
            case VanillaBlocks::SPRUCE_LEAVES():
                $max = 100;
                if($item->equals(VanillaItems::SHEARS())){
                    $max /= 2;
                }
                if(mt_rand(0, $max) < GameSettings::getInstance()->getAppleRate()){
                    $event->setDropsVariadic(VanillaItems::APPLE()->setCount(mt_rand(1, 2)), VanillaItems::STRING()->setCount(1));
                }elseif(mt_rand(0, $max) === GameSettings::getInstance()->getAppleRate()){
                    $event->setDropsVariadic(VanillaItems::GOLDEN_APPLE()->setCount(mt_rand(1, 4)), VanillaItems::STRING()->setCount(1));
                }
            break;
        }
    }

    /**
     * @param EntityDamageEvent $event
     * @return void
     */
    public function onEntityDamageEvent(EntityDamageEvent $event) : void {
        if(GameArena::getInstance()->getStatus() !== GameStatus::RUNNING){
            $event->cancel();
            return;
        }
        if(GameArena::getInstance()->isInGracePeriod()){
            $event->cancel();
            return;
        }
        if(GameArena::getInstance()->hasInvincibility()){
            switch($event->getCause()){
                case $event::CAUSE_ENTITY_ATTACK:
                case $event::CAUSE_PROJECTILE:
                case $event::CAUSE_BLOCK_EXPLOSION:
                    $event->cancel();
                break;
            }
        }
        $player = $event->getEntity();
        if(GameArena::getInstance()->getGamemodeType() === GamemodeType::TEAMS() && $event instanceof EntityDamageByEntityEvent){
            $attacker = $event->getDamager();
            if(!($attacker instanceof Player && $player instanceof Player)) return;

            if(TeamFactory::getInstance()->equalsExact(SessionFactory::getInstance()->getSession($attacker->getName()), SessionFactory::getInstance()->getSession($player->getName()))){
                $event->cancel();
            }
        }
    }

    /**
     * @param EntityRegainHealthEvent $event
     * @return void
     */
    public function onEntityRegainHealthEvent(EntityRegainHealthEvent $event) : void {
        $player = $event->getEntity();
        if(!$player instanceof Player) return;

        if(GameArena::getInstance()->getStatus() === GameStatus::RUNNING){
            if(!$player->getEffects()->has(VanillaEffects::REGENERATION())){
                $event->cancel();
            }
        }
    }

    /**
     * @param PlayerExhaustEvent $event
     * @return void
     */
    public function onPlayerExhaustEvent(PlayerExhaustEvent $event) : void {
        if(GameArena::getInstance()->getStatus() !== GameStatus::RUNNING){
            $event->cancel();
        }
    }

    /**
     * @param PlayerItemConsumeEvent $event
     * @return void
     */
    public function onPlayerItemConsumeEvent(PlayerItemConsumeEvent $event) : void {
        $player = $event->getPlayer();
        $item = $event->getItem();
        if($item instanceof GoldenApple && $item->getNamedTag()->getTag("golden_head") !== null){
            $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 10 * 20, 1));
        }
    }

    /**
     * @param PlayerMoveEvent $event
     * @return void
     */
    public function onPlayerMoveEvent(PlayerMoveEvent $event) : void {
        $player = $event->getPlayer();
        $from = $event->getFrom();
        $to = $event->getTo();

        if(GameArena::getInstance()->getStatus() !== GameStatus::RUNNING) return;

        if($from->distanceSquared($to) < GameArena::getInstance()->getRequiredPosition()) return;

        if(!GameArena::getInstance()->isBorderLimit($player->getPosition())){
            $player->teleport(GameArena::getInstance()->correctPosition($player->getPosition()));
        }
        GameArena::getInstance()->buildWall($player);
    }

    /**
     * @param PlayerItemHeldEvent $event
     * @return void
     */
    public function onPlayerInteractEvent(PlayerItemHeldEvent $event) : void {
        $player = $event->getPlayer();
        $item = $event->getItem();
        if($item->getId() === (VanillaItems::COMPASS())->getId() && $item->getCustomName() === TextHelper::replace("&r&l&aTELEPORTER")){
            GameSettingsForm::getInstance()->game_players_list($player);
        }
    }

    /**
     * @param PlayerDeathEvent $event
     * @return void
     */
    public function onPlayerDeathEvent(PlayerDeathEvent $event) : void {
        $player = $event->getPlayer();
        $deathCause = $player->getLastDamageCause();
        if(!($session = SessionFactory::getInstance()->getSession($player->getName())) instanceof Session || GameArena::getInstance()->getStatus() !== GameStatus::RUNNING) return;

        if(!ScenarioFactory::getInstance()->isActive(GameScenarios::TIME_BOMB)){
            $session->genGrave();
        }
        $session->setSpectador(true);

        if($deathCause instanceof EntityDamageByEntityEvent){
            if(!($attacker = $deathCause->getDamager()) instanceof Player) return;

            if(!($attackerSession = SessionFactory::getInstance()->getSession($attacker->getName())) instanceof Session) return;
        }
        switch($deathCause->getCause()){
            case EntityDamageEvent::CAUSE_CONTACT:
                if($deathCause instanceof EntityDamageByBlockEvent){
                    if($deathCause->getDamager()->getIdInfo()->getBlockId() === BlockLegacyIds::CACTUS){
                        $event->setDeathMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-death-by-cause-contact"), [
                            "player_name" => $player->getName(),
                            "player_kills" => $session->getKills(),
                        ]));
                    }
                }
            break;
            case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
                if($deathCause instanceof EntityDamageByEntityEvent){
                    $attackerSession->incrementKills();
                    $event->setDeathMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-death-by-cause-entity-attack"), [
                        "player_name" => $player->getName(),
                        "player_kills" => $session->getKills(),

                        "attacker_name" => $attacker->getName(),
                        "attacker_kills" => $attackerSession->getKills(),
                    ]));
                }
            break;
            case EntityDamageEvent::CAUSE_PROJECTILE:
                if($deathCause instanceof EntityDamageByEntityEvent){
                    $attackerSession->incrementKills();
                    $event->setDeathMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-death-by-cause-projectile"), [
                        "player_name" => $player->getName(),
                        "player_kills" => $session->getKills(),

                        "attacker_name" => $attacker->getName(),
                        "attacker_kills" => $attackerSession->getKills(),
                    ]));
                }
            break;
            case EntityDamageEvent::CAUSE_SUFFOCATION:
                $event->setDeathMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-death-by-cause-suffocation"), [
                    "player_name" => $player->getName(),
                    "player_kills" => $session->getKills(),
                ]));
                break;
            case EntityDamageEvent::CAUSE_FALL:
                $event->setDeathMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-death-by-cause-fall"), [
                    "player_name" => $player->getName(),
                    "player_kills" => $session->getKills(),
                ]));
            break;
            case EntityDamageEvent::CAUSE_FIRE:
            case EntityDamageEvent::CAUSE_FIRE_TICK:
                $event->setDeathMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-death-by-cause-fire"), [
                    "player_name" => $player->getName(),
                    "player_kills" => $session->getKills(),
                ]));
            break;
            case EntityDamageEvent::CAUSE_LAVA:
                $event->setDeathMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-death-by-cause-lava"), [
                    "player_name" => $player->getName(),
                    "player_kills" => $session->getKills(),
                ]));
            break;
            case EntityDamageEvent::CAUSE_DROWNING:
                $event->setDeathMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-death-by-cause-drowning"), [
                    "player_name" => $player->getName(),
                    "player_kills" => $session->getKills(),
                ]));
            break;
            case EntityDamageEvent::CAUSE_BLOCK_EXPLOSION:
            case EntityDamageEvent::CAUSE_ENTITY_EXPLOSION:
                $event->setDeathMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-death-by-cause-explosion"), [
                    "player_name" => $player->getName(),
                    "player_kills" => $session->getKills(),
                ]));
            break;
            case EntityDamageEvent::CAUSE_VOID:
                $event->setDeathMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-death-by-cause-void"), [
                    "player_name" => $player->getName(),
                    "player_kills" => $session->getKills(),
                ]));
            break;
            case EntityDamageEvent::CAUSE_SUICIDE:
                $event->setDeathMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-death-by-cause-suicide"), [
                    "player_name" => $player->getName(),
                    "player_kills" => $session->getKills(),
                ]));
            break;
            case EntityDamageEvent::CAUSE_MAGIC:
                $event->setDeathMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-death-by-cause-magic"), [
                    "player_name" => $player->getName(),
                    "player_kills" => $session->getKills(),
                ]));
            break;
            default:
                $event->setDeathMessage(TextHelper::replace(TextHelper::getMessageFile()->get("player-death-by-cause-default"), [
                    "player_name" => $player->getName(),
                    "player_kills" => $session->getKills(),
                ]));
            break;
        }
    }

    /**
     * @param DataPacketReceiveEvent $event
     * @return void
     */
    public function onDataPacketReceiveEvent(DataPacketReceiveEvent $event) : void {
        if(!($player = $event->getOrigin()->getPlayer()) instanceof Player) return;

        if(!($session = SessionFactory::getInstance()->getSession($player->getName())) instanceof Session) return;

        if($player->getCurrentWindow() instanceof EnchantInventory){
            $packet = $event->getPacket();
            if(($handler = InventoryFactory::getInstance()->get($packet->getName())) !== null){
                $handler->handle($session, $packet);
            }
        }
    }
}

?>