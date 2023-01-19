<?php 

namespace UHC\utils;

use UHC\providers\YamlProvider;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TE;

final class TextHelper {

    /**
     * @param string|array $message
     * @param array $findMessage
     * @param bool $isArray
     * @return string
     */
    public static function replace(string|array $message, array $findMessage = [], bool $isArray = true) : string {
        $replace = function(array $findMessage, string|array $message) use ($isArray) : string {
            if(is_array($message) && $isArray){
                return self::replace(implode("\n", $message), $findMessage);
            }
            foreach($findMessage as $search => $replace){
                $message = str_replace("{".$search."}", $replace, $message);
            }
            return str_replace([":bored:", ":panic:", ":L:", ":kiss:", ":angry:", ":cry:", ":heart:", ":broken_heart:", ":zzz:", ":peace:", ":fire:", ":frog:"], ["", "", "", "", "", "", "", "", "", "", "", ""], TE::colorize($message));
        };
        return $replace($findMessage, $message);
    }

    /**
     * @return Config
     */
    public static function getMessageFile() : Config {
        return YamlProvider::$messages;
    }
}

?>
