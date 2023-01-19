<?php

namespace UHC\world\inventory\transaction;

use pocketmine\utils\SingletonTrait;
use UHC\world\inventory\transaction\utils\TransactionHandler;
use UHC\world\inventory\handler\InventoryTransactionHandler;

class InventoryFactory {
    use SingletonTrait;

    /** @var array<string, TransactionHandler> */
    protected array $transactions = [];

    /**
     * @param TransactionHandler $transactionHandler
     * @return TransactionHandler
     */
    public function add(TransactionHandler $transactionHandler) : TransactionHandler {
        $this->transactions[$transactionHandler->getClassName()] = $transactionHandler;

        return $this->transactions[$transactionHandler->getClassName()] = $transactionHandler;
    }

    /**
     * @param string $name
     * @return void
     */
    public function remove(string $name) : void {
        unset($this->transactions[$name]);
    }

    /**
     * @param string $name
     * @return TransactionHandler|null
     */
    public function get(string $name) : ?TransactionHandler {
        return $this->transactions[$name] ?? null;
    }

    /**
     * @return TransactionHandler[]
     */
    public function getAll() : array {
        return $this->transactions;
    }

    /**
     * @return void
     */
    public function load() : void {
        $this->add(new InventoryTransactionHandler());
    }
}

?>