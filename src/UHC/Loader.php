<?php

namespace UHC;

use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;

use pocketmine\command\Command;
use pocketmine\event\Listener;

use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\item\ToolTier;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;

use pocketmine\crafting\ShapedRecipe;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockToolType;

use pocketmine\block\tile\EnchantTable as TileEnchantingTable;

use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;

use pocketmine\world\World;
use pocketmine\nbt\tag\CompoundTag;

use UHC\providers\YamlProvider;

use UHC\command\uhc\UhcCommand;
use UHC\command\team\TeamCommand;

use UHC\command\ReloadWorldsCommand;
use UHC\command\ScenariosCommand;
use UHC\command\SpectateCommand;

use UHC\item\FishingRod;

use UHC\world\block\TNT;
use UHC\world\entities\FishingHook;

use UHC\world\block\Anvil;
use UHC\world\block\EnchantingTable;

use UHC\listener\GameListener;

use UHC\task\GameUpdaterTask;
use UHC\task\PlayerNameTagUpdaterTask;

use UHC\arena\scenario\ScenarioFactory;
use UHC\world\inventory\transaction\InventoryFactory;

use UHC\world\entities\PrimedTNT;
use UHC\world\entities\Zombie;

use UHC\utils\DefaultPermissionNames;

class Loader extends PluginBase {

    /** @var Loader */
    protected static Loader $instance;

    /** @var Config */
    protected Config $recipes;

    /**
     * @return void
     */
    public function onLoad() : void {
        self::$instance = $this;
        YamlProvider::getInstance()->gen();
    }

    /**
     * @return void
     */
    public function onEnable() : void {
        $result = $this->getServer()->getCraftingManager();
        /** @noinspection PhpClosureCanBeConvertedToFirstClassCallableInspection */
        $itemDeserializerFunc = \Closure::fromCallable([Item::class, 'jsonDeserialize']);

        $this->recipes = new Config($this->getDataFolder()."recipe.json", Config::JSON);
        foreach($this->recipes->get("recipe") as $recipe){
            $result->registerShapedRecipe(new ShapedRecipe(
                $recipe["shape"],
                array_map($itemDeserializerFunc, $recipe["input"]),
                array_map($itemDeserializerFunc, $recipe["output"]),
                )
            );
        }
        ScenarioFactory::getInstance()->load();
        InventoryFactory::getInstance()->load();

        TexturePackLoader::getInstance()->load();

        $this->registerCommand(
            new UhcCommand("uhc", "Uhc management", DefaultPermissionNames::COMMAND_UHC, []),
            new TeamCommand("team", "Create team or select any team to join", null, []),
            new ReloadWorldsCommand("reloadworlds", "RLW management", DefaultPermissionNames::COMMAND_RLW, ["rlw"]),
            new ScenariosCommand("scenarios", "Scenarios available", null, []),
            new SpectateCommand("spectate", "Spectate a current game", null, []),
        );
        $this->registerListener(
            new GameListener(),
            new MainListener(),
        );

        $this->getScheduler()->scheduleRepeatingTask(new GameUpdaterTask(), 20);
        $this->getScheduler()->scheduleRepeatingTask(new PlayerNameTagUpdaterTask(), 60);

        ItemFactory::getInstance()->register(new FishingRod(new ItemIdentifier(ItemIds::FISHING_ROD, 0), "Fishing Rod"), true);

        BlockFactory::getInstance()->register(new TNT(new BlockIdentifier(BlockLegacyIds::TNT, 0), "TNT", BlockBreakInfo::instant()), true);
        BlockFactory::getInstance()->register(new Anvil(new BlockIdentifier(BlockLegacyIds::ANVIL, 0, null, null), "Anvil", new BlockBreakInfo(5.0, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 6000.0)), true);
        BlockFactory::getInstance()->register(new EnchantingTable(new BlockIdentifier(BlockLegacyIds::ENCHANTING_TABLE, 0, null, TileEnchantingTable::class), "Enchanting Table", new BlockBreakInfo(5.0, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 6000.0)), true);

        EntityFactory::getInstance()->register(PrimedTNT::class, function(World $world, CompoundTag $nbt) : PrimedTNT {
            return new PrimedTNT(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ['PrimedTNT', PrimedTNT::getNetworkTypeId()]);
        EntityFactory::getInstance()->register(FishingHook::class, function(World $world, CompoundTag $nbt) : FishingHook {
            return new FishingHook(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ['FishingHook', FishingHook::getNetworkTypeId()]);
        EntityFactory::getInstance()->register(Zombie::class, function(World $world, CompoundTag $nbt) : Zombie {
            return new Zombie(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ['Zombie', Zombie::getNetworkTypeId()]);
    }

    /**
     * @return void
     */
    public function onDisable() : void {

    }

    /**
     * @param Command ...$commands
     * @return void
     */
    public function registerCommand(Command ...$commands) : void {
        foreach($commands as $command){
            $this->getServer()->getCommandMap()->register("uhc", $command);
        }
    }

    /**
     * @param Listener ...$listeners
     * @return void
     */
    public function registerListener(Listener ...$listeners) : void {
        foreach($listeners as $listener){
            $this->getServer()->getPluginManager()->registerEvents($listener, $this);
        }
    }

    /**
     * @internal
     * @return Config
     */
    public function getRecipes() : Config {
        return $this->recipes;
    }

    /**
     * @return static
     */
    public static function getInstance() : self {
        return self::$instance;
    }
}

?>