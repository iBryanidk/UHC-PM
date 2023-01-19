<?php

namespace UHC\api\form;

use pocketmine\player\Player;
use pocketmine\form\Form;

abstract class IForm implements Form {

	/** @var callable|null */
	protected $callable;

	/** @var array */
	protected array $data = [];

    /**
     * IFrom Constructor.
     * @param callable|null $callable
     */
	public function __construct(?callable $callable){
		$this->callable = $callable;
	}

    /**
     * @return callable|null
     */
	public function getCallable() : ?callable {
		return $this->callable;
	}

	/**
	 * @param callable|null $callable
	 */
	public function setCallable(?callable $callable){
		$this->callable = $callable;
	}

    /**
     * @param Player $player
     * @param $data
     * @return void
     */
	public function handleResponse(Player $player, $data) : void {
		$this->processDataForm($data);
		$callable = $this->getCallable();
		if($callable !== null){
			$callable($player, $data);
		}
	}

    /**
     * @param $data
     * @return void
     */
	public function processDataForm(&$data) : void {
		
	}

    /**
     * @return array
     */
	public function jsonSerialize() : array {
		return $this->data;
	}
}

?>
