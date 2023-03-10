<?php /** @noinspection PhpArrayPushWithOneElementInspection */

namespace UHC\utils;

use UHC\session\Session;
use UHC\session\SessionFactory;

use UHC\api\Scoreboards;

use UHC\arena\game\GameArena;
use UHC\arena\game\utils\GamemodeType;
use UHC\arena\game\utils\GameStatus;

final class ScoreboardBuilder {

    /**
     * @param Session $session
     * @return void
     */
    public static function build(Session $session) : void {
        $lines = [];
        if(GameArena::getInstance()->getStatus() === GameStatus::WAITING){
            Scoreboards::getInstance()->removeLines($session->getPlayerNonNull());

            $lines[] = "&rStatus: ".(GameArena::getInstance()->getWorld() !== null ? GameStatus::fromId(GameStatus::WAITING) : GameStatus::fromId(GameStatus::UNKNOWN));
            if(count(GameArena::getInstance()->getScenarios()) !== 0){
                $lines[] = "&rScenarios: ";
                foreach(GameArena::getInstance()->getScenarios() as $scenario){
                    $lines[] = "- ".$scenario;
                }
            }
        }else{
            switch(GameArena::getInstance()->getStatus()){
                case GameStatus::PREPARING:
                    Scoreboards::getInstance()->removeLines($session->getPlayerNonNull());

                    $lines[] = "&rStatus: ".GameStatus::fromId(GameStatus::PREPARING);
                    $lines[] = "Preparing ends in: ".Time::getTimeToString(GameArena::getInstance()->getPreparingTime());

                    $lines[] = "";
                    $lines[] = "&rScenarios: ";
                    foreach(GameArena::getInstance()->getScenarios() as $scenario){
                        $lines[] = "- ".$scenario;
                    }
                break;
                case GameStatus::STARTING:
                    Scoreboards::getInstance()->removeLines($session->getPlayerNonNull());

                    $lines[] = "&rStatus: ".GameStatus::fromId(GameStatus::STARTING);
                    $lines[] = "&rStarting in: ".Time::getTimeToString(GameArena::getInstance()->getStartingTime());
                    $lines[] = "";
                    $lines[] = "&rHosted by: ".(($host = GameArena::getInstance()->getHost()) !== null ? $host->getName() : "Unknown");
                    $lines[] = "&rGamemode: ".GameArena::getInstance()->getGamemodeType()->__toString();
                    $lines[] = "&rScenarios: ";
                    foreach(GameArena::getInstance()->getScenarios() as $scenario){
                        $lines[] = "- ".$scenario;
                    }
                break;
                case GameStatus::RUNNING:
                    Scoreboards::getInstance()->removeLines($session->getPlayerNonNull());

                    $lines[] = "&rStatus: ".GameStatus::fromId(GameStatus::RUNNING);
                    $lines[] = "&rGame Time: ".Time::getTimeToString(GameArena::getInstance()->getRunningTime());
                    $lines[] = "";
                    $lines[] = "Remaining: ".count(GameArena::getInstance()->getRemainingPlayers());
                    if(GameArena::getInstance()->getGamemodeType() === GamemodeType::TEAMS()){
                        $lines[] = "Teams remaining: ".count(GameArena::getInstance()->getRemainingTeams());
                    }
                    $lines[] = "&r";
                    if(GameArena::getInstance()->isInGracePeriod()){
                        $lines[] = "&rGrace period: ".Time::getTimeToString(40 - GameArena::getInstance()->getRunningTime());
                    }
                    if(GameArena::getInstance()->hasInvincibility()){
                        $lines[] = "&rPvP: ".Time::getTimeToString(1200 - GameArena::getInstance()->getRunningTime());
                    }
                    $lines[] = "&rBorder: ".GameArena::getInstance()->getBorder();

                    if($session->isHost()){
                        $lines[] = "&rSpectators: ".count(array_filter(SessionFactory::getInstance()->getSessions(), fn(Session $session) => $session->isSpectador()));
                    }
                break;
                case GameStatus::ENDING:
                    Scoreboards::getInstance()->removeLines($session->getPlayerNonNull());

                    $lines[] = "&rStatus: ".GameStatus::fromId(GameStatus::ENDING);
                    $lines[] = "&rEnding in: ".Time::getTimeToString(GameArena::getInstance()->getEndingTime());
                    $lines[] = "";
                    $lines[] = (count($winner = GameArena::getInstance()->findWinner()) !== 0) ? "Winner: " : "There is no winner";
                    foreach($winner as $index => $winnerInfo){
                        $lines[] = $winnerInfo;
                    }
                break;
            }
        }
        array_unshift($lines, "&r&7---------------------");
        array_push($lines, "&r&7&7---------------------");

        Scoreboards::getInstance()->add($session->getPlayerNonNull(), TextHelper::replace("&r&l&dUHC", [], false));
        foreach($lines as $line => $newLine){
            Scoreboards::getInstance()->addLine($session->getPlayerNonNull(), $line + 1, $newLine);
        }
    }
}

?>