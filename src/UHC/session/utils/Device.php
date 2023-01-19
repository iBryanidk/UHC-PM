<?php

namespace UHC\session\utils;

final class Device {

    /** @var string[] */
    public static array $unavailable = ["Win10", "Win32", "PlayStation"];

    /** @var string[] */
    protected static array $ids = ["Unknown", "Android", "iOS", "OSX", "Amazon", "GearVR", "Hololens", "Win10", "Win32", "Dedicated", "TVOS", "PlayStation", "Nintendo", "Xbox", "Windows Phone"];

    /**
     * @param int $id
     * @return string
     */
    public static function fromId(int $id) : string {
        return self::$ids[$id] ?? "Unknown";
    }
}

?>