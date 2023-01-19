<?php

namespace UHC\session\utils;

final class DeviceModel {

    /** @var string[] */
    public static array $unavailable = ["Keyboard", "Controller"];

    /** @var string[] */
    protected static array $ids = ["Unknown", "Keyboard", "Touch", "Controller"];

    /**
     * @param int $id
     * @return string
     */
    public static function fromId(int $id) : string {
        return self::$ids[$id] ?? "Unknown";
    }
}

?>