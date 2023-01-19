<?php

namespace UHC\api\discord;

use UHC\Loader;
use UHC\api\discord\async\DiscordSendMessage;

use pocketmine\plugin\PluginException;

class Discord {

    /**
     * @param IDiscord $content
     * @return void
     */
    public static function send(IDiscord $content) : void {
        Loader::getInstance()->getServer()->getAsyncPool()->submitTask(new DiscordSendMessage($content->getURL(), $content, $content->getUsername()));
    }

    /**
     * @return bool
     */
    public static function log() : bool {
        return boolval(Loader::getInstance()->getConfig()->getNested("discord.log"));
    }

    /**
     * @return string
     */
    public static function getUsername() : string {
        return ($username = Loader::getInstance()->getConfig()->getNested("discord.username")) !== "" ? $username : throw new PluginException("Discord username can't be null");
    }

    /**
     * @return string
     */
    public static function getURL() : string {
        return ($url = Loader::getInstance()->getConfig()->getNested("discord.url")) !== "" ? $url : throw new PluginException("Discord url can't be null");
    }
}

?>