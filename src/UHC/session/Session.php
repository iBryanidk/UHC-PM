<?php

namespace UHC\session;

use UHC\Loader;
use UHC\utils\TextHelper;

use UHC\arena\team\Team;

use UHC\session\utils\Device;
use UHC\session\utils\DeviceModel;
use UHC\session\utils\ReconnectUtils;

use UHC\world\inventory\transaction\EnchantingTransaction;

use pocketmine\Server;
use pocketmine\player\Player;

use pocketmine\world\Position;
use pocketmine\player\GameMode;

use pocketmine\scheduler\ClosureTask;
use pocketmine\plugin\PluginException;

use pocketmine\item\VanillaItems;
use pocketmine\block\VanillaBlocks;

use pocketmine\network\mcpe\protocol\types\BoolGameRule;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;

class Session {
    use ReconnectUtils;

    /** @var bool */
    protected bool $spectador = false;

    /** @var bool */
    protected bool $host = false;

    /** @var bool */
    protected bool $scattering = false;

    /** @var int */
    protected int $kills = 0;

    /** @var Team|null */
    protected ?Team $team = null;

    /** @var Team|null */
    protected ?Team $teamInvite = null;

    /** @var EnchantingTransaction|null */
    protected ?EnchantingTransaction $enchantingTransaction = null;

    /**
     * Session Constructor.
     * @param string $name
     * @param int $id
     * @param string $rawUUID
     */
    public function __construct(
        protected string $name,
        protected int $id,
        protected string $rawUUID,
    ){}

    /**
     * @return string
     */
    public function getName() : string {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getId() : int {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getRawUUID() : string {
        return $this->rawUUID;
    }

    /**
     * @return Team|null
     */
    public function getTeam() : ?Team {
        return $this->team;
    }

    /**
     * @param Team|null $team
     * @return void
     */
    public function setTeam(Team $team = null) : void {
        $this->team = $team;
    }

    /**
     * @return Team|null
     */
    public function getTeamInvite() : ?Team {
        return $this->teamInvite;
    }

    /**
     * @param Team|null $team
     * @return void
     */
    public function setTeamInvite(Team $team = null) : void {
        $this->teamInvite = $team;
    }

    /**
     * @return EnchantingTransaction|null
     */
    public function getEnchantingTransaction() : ?EnchantingTransaction {
        return $this->enchantingTransaction;
    }

    /**
     * @param EnchantingTransaction|null $enchantingTransaction
     * @return void
     */
    public function setEnchantingTransaction(EnchantingTransaction $enchantingTransaction = null) : void {
        $this->enchantingTransaction = $enchantingTransaction;
    }

    /**
     * @return string
     */
    public function getDevice() : string {
        return Device::fromId($this->getPlayerNonNull()->getPlayerInfo()->getExtraData()["DeviceOS"]);
    }

    /**
     * @return string
     */
    public function getDeviceModel() : string {
        return DeviceModel::fromId($this->getPlayerNonNull()->getPlayerInfo()->getExtraData()["CurrentInputMode"]);
    }

    /**
     * @return bool
     */
    public function isSpectador() : bool {
        return $this->spectador && $this->getPlayerNonNull()->isSpectator();
    }

    /**
     * @param bool $spectador
     * @return void
     */
    public function setSpectador(bool $spectador) : void {
        $this->spectador = $spectador;

        if($spectador){
            Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() : void { $this->getPlayerNonNull()->getInventory()->setItem(4, VanillaItems::COMPASS()->setCustomName(TextHelper::replace("&r&l&aTELEPORTER"))); }), 5);
        }else{
            $this->getPlayerNonNull()->getInventory()->remove(VanillaItems::COMPASS());
        }
        $this->getPlayerNonNull()->setGamemode($spectador ? GameMode::SPECTATOR() : GameMode::SURVIVAL());
    }

    /**
     * @return void
     */
    public function genGrave() : void {
        $head = VanillaBlocks::CARVED_PUMPKIN();
        $base = VanillaBlocks::OAK_FENCE();

        $position = $this->getPlayerNonNull()->getPosition();
        if($position->getFloorY() < 0){
            return;
        }
        $position->getWorld()->setBlock($position, $base);
        $position->getWorld()->setBlock(($lastPosition = $position->add(0, 1, 0)), $base);

        $block = $position->getWorld()->getBlock($lastPosition);
        foreach($block->getHorizontalSides() as $side){
            $side->getPosition()->getWorld()->setBlock($side->getPosition(), $base);
        }
        $position->getWorld()->setBlock(new Position($block->getPosition()->getFloorX() + 0.5, $block->getPosition()->getFloorY() + 1, $block->getPosition()->getFloorZ() + 0.5, $block->getPosition()->getWorld()), $head);
    }

    /**
     * @return bool
     */
    public function isHost() : bool {
        return $this->host;
    }

    /**
     * @param bool $host
     * @return void
     */
    public function setHost(bool $host = false) : void {
        $this->host = $host;
    }

    /**
     * @return bool
     */
    public function isScattering() : bool {
        return $this->scattering;
    }

    /**
     * @param bool $scattering
     * @return void
     */
    public function setScattering(bool $scattering = false) : void {
        $this->scattering = $scattering;
    }

    /**
     * @return int
     */
    public function getKills() : int {
        return $this->kills;
    }

    /**
     * @return void
     */
    public function incrementKills() : void {
        $this->kills += 1;
    }

    /**
     * @return void
     */
    public function decrementKills() : void {
        $this->kills -= 1;
    }

    /**
     * @param int $kills
     * @return void
     */
    public function setKills(int $kills) : void {
        $this->kills = $kills;
    }

    /**
     * @return void
     */
    public function showCoordinates() : void {
        $this->getPlayerNonNull()->getNetworkSession()->sendDataPacket(GameRulesChangedPacket::create(["showcoordinates" => new BoolGameRule(true, false)]));
    }

    /**
     * @return Player|null
     */
    public function getPlayer() : ?Player {
        try {
            return $this->getPlayerNonNull();
        }catch(PluginException){
            return null;
        }
    }

    /**
     * @return Player
     */
    public function getPlayerNonNull() : Player {
        return Server::getInstance()->getPlayerByRawUUID($this->getRawUUID()) ?? throw new PluginException("Player is offline");
    }
}

?>