<?php

namespace UHC\world\inventory\transaction\utils;

use pocketmine\network\mcpe\protocol\ServerboundPacket;
use UHC\session\Session;

abstract class TransactionHandler {

    /**
     * TransactionHandler Constructor.
     * @param string $className
     */
    public function __construct(
        protected string $className
    ){
        $this->className = basename(str_replace("\\", "/", $className));
    }

    /**
     * @return string
     */
    public function getClassName(): string {
        return $this->className;
    }

    /**
     * @param Session $session
     * @param ServerboundPacket $packet
     * @return void
     */
    abstract public function handle(Session $session, ServerboundPacket $packet) : void;
}

?>