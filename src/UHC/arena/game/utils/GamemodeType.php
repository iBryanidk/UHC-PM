<?php

namespace UHC\arena\game\utils;

use pocketmine\utils\EnumTrait;

/**
 * @method static FFA()
 * @method static TEAMS()
 */
final class GamemodeType {
    use EnumTrait;

    /**
     * @return void
     */
    protected static function setup() : void {
        self::registerAll(
            new self("ffa"),
            new self("teams"),
        );
    }

    /**
     * @param string $name
     * @return static|null
     */
    public static function fromString(string $name) : ?self {
        $enums = array_filter(self::getAll(), fn($enum) => $enum->enumName === $name);
        foreach($enums as $enum){
            return $enum;
        }
        return null;
    }

    /**
     * @return array
     */
    public static function getTypes() : array {
        foreach(self::getAll() as $enum){
            $types[] = $enum->__toString();
        }
        return $types ?? [];
    }

    /**
     * @return string
     */
    public function __toString() : string {
        return strtoupper($this->enumName);
    }
}

?>