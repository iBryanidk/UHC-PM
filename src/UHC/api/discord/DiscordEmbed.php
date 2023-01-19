<?php

namespace UHC\api\discord;

class DiscordEmbed extends IDiscord {

    /**
     * DiscordEmbed Constructor.
     * @param string $title
     * @param string $description
     * @param int $color
     * @param array $fields
     */
    public function __construct(string $title = "", string $description = "", int $color = 0xCC33FF, array $fields = []){
        $this->data = [
            "title" => $title,
            "description" => $description,
            "color" => $color,
            "fields" => $fields,
        ];
    }

    /**
     * @return string
     */
    public function getTitle() : string {
        return $this->data["title"] ?? "";
    }

    /**
     * @param string $title
     * @return void
     */
    public function setTitle(string $title) : void {
        $this->data["title"] = $title;
    }

    /**
     * @return string
     */
    public function getDescription() : string {
        return $this->data["description"] ?? "";
    }

    /**
     * @param string $description
     * @return void
     */
    public function setDescription(string $description) : void {
        $this->data["description"] = $description;
    }

    /**
     * @return int
     */
    public function getColor() : int {
        return $this->data["color"] ?? 0x0DEBD7;
    }

    /**
     * @param int $color
     * @return void
     */
    public function setColor(int $color) : void {
        $this->data["color"] = $color;
    }

    /**
     * @param string $name
     * @param string $value
     * @param bool $inLine
     * @return void
     */
    public function addField(string $name, string $value, bool $inLine = false) : void {
        $this->data["fields"][] = [
            "name" => $name,
            "value" => $value,
            "inline" => $inLine,
        ];
    }
}

?>