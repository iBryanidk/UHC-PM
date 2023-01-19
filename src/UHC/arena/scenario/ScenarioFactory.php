<?php

namespace UHC\arena\scenario;

use pocketmine\utils\SingletonTrait;

use UHC\Loader;
use UHC\arena\scenario\type\BloodDiamondScenario;
use UHC\arena\scenario\type\BowlessScenario;
use UHC\arena\scenario\type\CatEyesScenario;
use UHC\arena\scenario\type\CutCleanScenario;
use UHC\arena\scenario\type\DiamondBombScenario;
use UHC\arena\scenario\type\DiamondlessScenario;
use UHC\arena\scenario\type\FirelessScenario;
use UHC\arena\scenario\type\GoldlessScenario;
use UHC\arena\scenario\type\HasteyBoysScenario;
use UHC\arena\scenario\type\NoFallScenario;
use UHC\arena\scenario\type\RodlessScenario;
use UHC\arena\scenario\type\SnowlessScenario;
use UHC\arena\scenario\type\TimberScenario;
use UHC\arena\scenario\type\TimeBombScenario;

class ScenarioFactory {
    use SingletonTrait;

    /** @var array<string, Scenario> */
    protected array $scenario = [];

    /**
     * @param Scenario $scenario
     * @return Scenario
     */
    public function add(Scenario $scenario) : Scenario {
        $this->scenario[$scenario->getName()] = $scenario;

        return $this->scenario[$scenario->getName()];
    }

    /**
     * @param string $name
     * @return void
     */
    public function remove(string $name) : void {
        unset($this->scenario[$name]);
    }

    /**
     * @param string $name
     * @return Scenario|null
     */
    public function get(string $name) : ?Scenario {
        return $this->scenario[$name] ?? null;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isActive(string $name) : bool {
        return $this->get($name)->isActive();
    }

    /**
     * @return Scenario[]
     */
    public function getAll() : array {
        return $this->scenario;
    }

    /**
     * @return void
     */
    public function load() : void {
        $this->add(new NoFallScenario());
        $this->add(new TimeBombScenario());
        $this->add(new CutCleanScenario());
        $this->add(new TimberScenario());
        $this->add(new CatEyesScenario());
        $this->add(new BowlessScenario());
        $this->add(new FirelessScenario());
        $this->add(new RodlessScenario());
        $this->add(new SnowlessScenario());
        $this->add(new DiamondlessScenario());
        $this->add(new GoldlessScenario());
        $this->add(new HasteyBoysScenario());
        $this->add(new BloodDiamondScenario());
        $this->add(new DiamondBombScenario());

        foreach($this->getAll() as $scenario){
            Loader::getInstance()->getServer()->getPluginManager()->registerEvents(new $scenario, Loader::getInstance());
        }
    }
}

?>