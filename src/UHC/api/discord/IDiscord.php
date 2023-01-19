<?php

namespace UHC\api\discord;

abstract class IDiscord implements \JsonSerializable {

    /** @var array */
    protected array $data = [];

    /**
     * @return mixed
     */
    public function jsonSerialize() : array {
        return $this->data;
    }
}

?>