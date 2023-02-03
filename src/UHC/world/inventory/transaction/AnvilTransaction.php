<?php

namespace UHC\world\inventory\transaction;

use UHC\session\Session;

use UHC\world\inventory\AnvilInventory;
use UHC\world\inventory\action\AnvilAction;

use pocketmine\player\Player;
use pocketmine\plugin\PluginException;

use pocketmine\item\Tool;
use pocketmine\item\Sword;
use pocketmine\item\ToolTier;
use pocketmine\item\TieredTool;

use pocketmine\item\Armor;
use pocketmine\item\Durable;

use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;

use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;

use pocketmine\block\BlockToolType;
use pocketmine\block\VanillaBlocks;

use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\inventory\transaction\action\InventoryAction;
use pocketmine\inventory\transaction\action\SlotChangeAction;

class AnvilTransaction extends InventoryTransaction {

    const USES_TAG = "Uses";
    const COST_TAG = "RepairCost";

    /** @var int */
    protected int $cost = -1;

    /** @var string */
    protected string $name = "";

    /** @var Item|null */
    protected ?Item $target = null;

    /** @var Item|null */
    protected ?Item $sacrifice = null;

    /** @var Item|null */
    protected ?Item $result = null;

    /**
     * AnvilTransaction Constructor.
     * @param Player $source
     * @param Session $session
     * @param array $actions
     */
    public function __construct(
        Player $source,
        protected Session $session,
        array $actions = []
    ){
        parent::__construct($source, $actions);
    }

    /**
     * @return string
     */
    public function getName() : string {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getCost() : int {
        return $this->cost;
    }

    /**
     * @param int $cost
     */
    public function setCost(int $cost) : void {
        $this->cost = $cost;
    }

    /**
     * @param Item|null $target
     * @return void
     */
    public function setTarget(?Item $target) : void {
        $this->target = $target;
    }

    /**
     * @return bool
     */
    public function hasTarget() : bool {
        return $this->target instanceof Item && !$this->target->isNull();
    }

    /**
     * @param Item|null $sacrifice
     * @return void
     */
    public function setSacrifice(?Item $sacrifice) : void {
        $this->sacrifice = $sacrifice;
    }

    /**
     * @return Item|null
     */
    public function getResult() : ?Item {
        return $this->result;
    }

    /**
     * @param Item|null $result
     * @return void
     */
    public function setResult(?Item $result) : void {
        $this->result = $result;
    }

    /**
     * @return bool
     */
    public function hasResult() : bool {
        return $this->result instanceof Item && !$this->result->isNull();
    }

    /**
     * @param Item $item
     * @return int
     */
    public function getRepairCost(Item $item) : int {
        return $item->getNamedTag()->getInt(self::COST_TAG, 0);
    }

    /**
     * @param Item $item
     * @param int $cost
     * @return void
     */
    public function setRepairCost(Item $item, int $cost) : void {
        if($cost <= 0){
            $item->getNamedTag()->removeTag(self::COST_TAG);
            return;
        }
        $item->getNamedTag()->setInt(self::COST_TAG, $cost);
    }

    /**
     * @param Item $item
     * @return int
     */
    public function getUses(Item $item) : int {
        if(($uses = $item->getNamedTag()->getInt(self::USES_TAG, -1)) !== -1){
            return $uses;
        }
        $repairCost = $this->getRepairCost($item);

        $uses = log($repairCost + 1) / log(2);
        $item->getNamedTag()->setInt(self::USES_TAG, $uses);

        return $uses;
    }

    /**
     * @param Item $item
     * @param int $uses
     * @return void
     */
    public function setUses(Item $item, int $uses) : void {
        if($uses <= 0){
            $item->getNamedTag()->removeTag(self::USES_TAG);
            return;
        }
        $item->getNamedTag()->setInt(self::USES_TAG, $uses);
    }

    /**
     * @return void
     */
    public function validate() : void {
        $this->squashDuplicateSlotChanges();
        if(count($this->actions) < 3){
            throw new PluginException("Transaction must have at least three actions to be executable");
        }
        foreach($this->actions as $action){

            if(!$action instanceof AnvilAction) continue;

            $action->validate($this->getSource());
        }
        $haveItems = [];
        $needItems = [];

        $this->matchItems($needItems, $haveItems);
        $this->updateItems();

        if(!$this->hasTarget()){
            throw new PluginException("Missing target item for transaction");
        }
        $this->checkResult();
    }

    /**
     * @return void
     */
    public function updateItems() : void {
        foreach($this->actions as $action){
            if($action instanceof SlotChangeAction && $action->getInventory() instanceof AnvilInventory && !$action->getTargetItem()->isNull()){
                switch($action->getSlot()){
                    case AnvilInventory::TARGET:
                        $this->setTarget($action->getTargetItem());
                    break;
                    case AnvilInventory::SACRIFICE:
                        $this->setSacrifice($action->getTargetItem());
                    break;
                    default:
                        throw new PluginException("Invalid slot ({$action->getSlot()}) supplied to anvil transaction");
                }
            }
        }
    }

    /**
     * @param Item $target
     * @param Item|null $sacrifice
     * @return int
     */
    public function calculateTransactionCost(Item $target, ?Item $sacrifice = null) : int {

        $targetUses = $this->getUses($target);
        $cost = (2 ** $targetUses) - 1;

        if($sacrifice !== null){
            if($target instanceof Durable){
                if($sacrifice instanceof Durable){
                    $sacrificeUses = $this->getUses($sacrifice);
                    $cost += (2 ** $sacrificeUses) - 1;
                }
                if($this->shouldRepair($target, $sacrifice)){
                    $cost += 2;
                }
            }
        }
        if($this->name !== ""){
            $cost += 1;
        }
        return $cost;
    }

    /**
     * @param int $uses
     * @return int
     */
    public function calculateRepairCost(int $uses) : int {
        return (2 ** $uses) - 1;
    }

    /**
     * @return void
     */
    public function checkResult() : void {
        if(!$this->hasResult()){
            throw new PluginException("Transaction has no pending result");
        }
        $result = $this->calculateResult($this->target, $this->sacrifice);
        $this->setUses($this->result, $this->getUses($result));

        if(!$result->equalsExact($this->result)){
            throw new PluginException("Calculated result ($result) does not match output item ($this->result)");
        }
    }

    /**
     * @param Item $target
     * @param Item|null $sacrifice
     * @return Item
     */
    public function calculateResult(Item $target, ?Item $sacrifice = null) : Item {
        $output = clone $target;

        if($this->name !== ""){
            $output->setCustomName($this->name);
        }
        $uses = $this->getUses($output);
        if($sacrifice !== null){
            if($output instanceof Durable){
                $output->setDamage($this->calculateDurability($output, $sacrifice));
            }
            if($sacrifice->equals($output, false, false) || ($sacrifice->getId() === ItemIds::ENCHANTED_BOOK)){
                if($sacrifice->hasEnchantments()){
                    foreach($sacrifice->getEnchantments() as $sacrificeEnchantment){
                        $type = $sacrificeEnchantment->getType();
                        if($output->hasEnchantment($type)){
                            $sacrificeLevel = $sacrificeEnchantment->getLevel();
                            $outputLevel = $output->getEnchantmentLevel($type);
                            $level = $sacrificeLevel > $outputLevel ? $sacrificeLevel : ($outputLevel === $sacrificeLevel ? $outputLevel + 1 : $outputLevel);
                            $enchantment = new EnchantmentInstance($type, min($level, $type->getMaxLevel()));
                        }elseif($this->isCompatible($output, $type) && $this->canApply($output, $type)){
                            $enchantment = clone $sacrificeEnchantment;
                        }else{
                            continue;
                        }
                        $output->addEnchantment($enchantment);
                    }
                }
                $uses = max($uses, $this->getUses($sacrifice));
            }
        }
        $uses += 1;
        $this->setUses($output, $uses);

        $cost = $this->calculateRepairCost($uses);
        $this->setRepairCost($output, $cost);

        return $output;
    }

    /**
     * @param Durable $target
     * @param Item $material
     * @return int
     */
    public function calculateDurability(Durable $target, Item $material) : int {
        $durability = $target->getDamage();
        if($material instanceof Durable && $material->equals($target, false, false)){
            $durability -= $material->getDamage();
        }elseif($material->equals($this->getRepairItem($target), true, false)){
            $reductionValue = (int) floor($durability * 0.12);
            $count = $material->getCount();
            while($count-- > 0){
                $durability -= $reductionValue;
            }
        }
        return max(0, $durability);
    }

    /**
     * @param Item $target
     * @param Enchantment $enchantment
     * @return bool
     */
    public function isCompatible(Item $target, Enchantment $enchantment) : bool {
        return false;
    }

    /**
     * @param Item $target
     * @param Enchantment $enchantment
     * @return bool
     */
    public function canApply(Item $target, Enchantment $enchantment) : bool {
        if($target instanceof Armor){
            $flag = match($target->getArmorSlot()){
                ArmorInventory::SLOT_HEAD => ItemFlags::HEAD,
                ArmorInventory::SLOT_CHEST => ItemFlags::TORSO,
                ArmorInventory::SLOT_LEGS => ItemFlags::LEGS,
                ArmorInventory::SLOT_FEET => ItemFlags::FEET
            };
            return $enchantment->hasPrimaryItemType($flag);
        }elseif($target instanceof Sword){
            return $enchantment->hasPrimaryItemType(ItemFlags::SWORD);
        }elseif($target instanceof Tool){
            $flag = match ($target->getBlockToolType()){
                BlockToolType::SHOVEL => ItemFlags::SHOVEL,
                BlockToolType::PICKAXE => ItemFlags::PICKAXE,
                BlockToolType::AXE => ItemFlags::AXE,
                default => ItemFlags::TOOL
            };
            return $enchantment->hasPrimaryItemType($flag);
        }
        return false;
    }

    /**
     * @param Durable $target
     * @param Item $sacrifice
     * @return bool
     */
    public function shouldRepair(Durable $target, Item $sacrifice) : bool {
        if($target->getDamage() <= 0){
            return false;
        }elseif($target->equals($sacrifice, false, false)){
            return true;
        }
        $repairItem = $this->getRepairItem($target);

        return $repairItem !== null && $sacrifice->equals($repairItem, false, false);
    }

    /**
     * @param Durable $target
     * @return Item|null
     */
    public function getRepairItem(Durable $target) : ?Item {
        if($target instanceof TieredTool){
            return match($target->getTier()->id()){
                ToolTier::WOOD()->id() => VanillaBlocks::OAK_PLANKS()->asItem(),
                ToolTier::STONE()->id() => VanillaBlocks::COBBLESTONE()->asItem(),
                ToolTier::GOLD()->id() => VanillaItems::GOLD_INGOT(),
                ToolTier::IRON()->id() => VanillaItems::IRON_INGOT(),
                ToolTier::DIAMOND()->id() => VanillaItems::DIAMOND(),
                default => null,
            };
        }
        return match($target->getId()){
            ItemIds::LEATHER_CAP, ItemIds::LEATHER_TUNIC, ItemIds::LEATHER_PANTS, ItemIds::LEATHER_BOOTS => VanillaItems::LEATHER(),
            ItemIds::IRON_HELMET, ItemIds::IRON_CHESTPLATE, ItemIds::IRON_LEGGINGS, ItemIds::IRON_BOOTS => VanillaItems::IRON_INGOT(),
            ItemIds::GOLD_HELMET, ItemIds::GOLD_CHESTPLATE, ItemIds::GOLD_LEGGINGS, ItemIds::GOLD_BOOTS => VanillaItems::GOLD_INGOT(),
            ItemIds::DIAMOND_HELMET, ItemIds::DIAMOND_CHESTPLATE, ItemIds::DIAMOND_LEGGINGS, ItemIds::DIAMOND_BOOTS => VanillaItems::DIAMOND(),
            ItemIds::TURTLE_HELMET => VanillaItems::SCUTE(),
            default => null,
        };
    }

    /**
     * @param AnvilInventory $inventory
     * @return void
     */
    public function onSuccess(AnvilInventory $inventory) : void {
        $this->cost = $this->calculateTransactionCost($this->target, $this->sacrifice);
        if(!$this->source->isCreative()){
            $this->source->getXpManager()->subtractXpLevels($this->cost);
        }
        $inventory->onSuccess($this->source);
    }
}

?>