<?php

namespace UHC\api;

use pocketmine\utils\SingletonTrait;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TE;

use pocketmine\network\mcpe\protocol\{RemoveObjectivePacket, SetDisplayObjectivePacket, SetScorePacket};
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;

class Scoreboards {
	use SingletonTrait;

    /** @var string */
	const LIST = "list";
     /** @var string */
	const SIDEBAR = "sidebar";
    /** @var string */
	const DUMMY = "dummy";

    /** @var int */
	const DESCENDING = 0;
    /** @var int */
	const ASCENDING = 1;
	
	/**
	 * @param Player $player
	 * @param string $title
	 * @return void
	 */
	public function add(Player $player, string $title) : void {
		$player->getNetworkSession()->sendDataPacket(SetDisplayObjectivePacket::create(
			self::SIDEBAR,
			$player->getName(),
			$title,
			self::DUMMY,
			self::DESCENDING,
		));
	}
	
	/**
	 * @param Player $player
	 * @return void
	 */
	public function remove(Player $player) : void {
		$player->getNetworkSession()->sendDataPacket(RemoveObjectivePacket::create($player->getName()));
	}

    /**
     * @param Player $player
     * @param int $slot
     * @param string $line
     * @return void
     */
	public function addLine(Player $player, int $slot, string $line) : void {
		$player->getNetworkSession()->sendDataPacket($this->getScorePacket($player, $slot, $line, SetScorePacket::TYPE_REMOVE));
		$player->getNetworkSession()->sendDataPacket($this->getScorePacket($player, $slot, $line, SetScorePacket::TYPE_CHANGE));
	}

    /**
     * @param Player $player
     * @return void
     */
    public function removeLines(Player $player) : void {
        for($i = 0; $i <= 15; $i++){
            $player->getNetworkSession()->sendDataPacket($this->getScorePacket($player, $i, "", SetScorePacket::TYPE_REMOVE));
        }
    }

    /**
     * @param Player $player
     * @param int $slot
     * @param string $line
     * @param int $type
     * @return SetScorePacket
     */
	public function getScorePacket(Player $player, int $slot, string $line, int $type) : SetScorePacket {
		$entry = new ScorePacketEntry();
		$entry->objectiveName = $player->getName();
		$entry->score = $slot;
		$entry->scoreboardId = $slot;
		$entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
		$entry->customName = " ".TE::colorize($line)." ";
		return SetScorePacket::create($type, [$entry]);
	}
}

?>

		
		
		
		
		