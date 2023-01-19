<?php

namespace UHC\api\discord;

class DiscordMessage extends IDiscord {

    /**
     * DiscordMessage Constructor.
     * @param string $content
     * @param string $url
     * @param string $username
     */
    public function __construct(string $content = "", string $url = "", string $username = ""){
        $this->data = [
            "content" => $content,
            "url" => $url,
            "username" => $username,
        ];
    }

    /**
     * @return string
     */
    public function getContent() : string {
        return $this->data["content"] ?? "";
    }

    /**
     * @param string $content
     * @return void
     */
    public function setContent(string $content) : void {
        $this->data["content"] = $content;
    }

    /**
     * @return string
     */
    public function getUsername() : string {
        return $this->data["username"];
    }

    /**
     * @param string $username
     * @return void
     */
    public function setUsername(string $username) : void {
        $this->data["username"] = $username;
    }

    /**
     * @return string
     */
    public function getURL() : string {
        return $this->data["url"];
    }

    /**
     * @param string $url
     * @return void
     */
    public function setURL(string $url) : void {
        $this->data["url"] = $url;
    }

    /**
     * @param DiscordEmbed $embed
     * @return void
     */
    public function setEmbed(DiscordEmbed $embed) : void {
        $this->data["embeds"][] = $embed;
    }
}

?>