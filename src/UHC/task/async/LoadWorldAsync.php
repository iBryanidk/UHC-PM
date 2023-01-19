<?php

namespace UHC\task\async;

use pocketmine\Server;
use UHC\utils\Zip;
use pocketmine\scheduler\AsyncTask;

class LoadWorldAsync extends AsyncTask {

    /**
     * LoadWorldAsync Constructor.
     * @param string $from
     * @param string $to
     * @param string $worldName
     */
    public function __construct(
        protected string $from,
        protected string $to,
        protected string $worldName,
    ){}

    /**
     * @return void
     */
    public function onRun() : void {
        try {
            Zip::decompress($this->from, $this->to, $this->worldName);
        } catch(\RuntimeException $exception){
            $this->worker->getLogger()->warning($exception->getMessage());
        } finally {
            $this->worker->getLogger()->warning("World-Async: [World: ".$this->worldName."] copied from {$this->from} to {$this->to}");
        }
    }

    /**
     * @return void
     */
    public function onCompletion() : void {
        Server::getInstance()->getWorldManager()->loadWorld($this->worldName);
    }
}

?>