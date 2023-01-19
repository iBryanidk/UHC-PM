<?php

namespace UHC\task;

use pocketmine\Server;
use UHC\session\Session;
use UHC\session\SessionFactory;
use UHC\utils\ScoreboardBuilder;
use UHC\utils\Time;
use UHC\utils\TextHelper;

use UHC\arena\game\GameArena;
use UHC\arena\game\utils\GamemodeType;
use UHC\arena\game\utils\GameScatter;
use UHC\arena\game\utils\GameStatus;

use UHC\event\GameEndEvent;
use UHC\event\GameStartEvent;

use pocketmine\scheduler\Task;

class GameUpdaterTask extends Task {

    /**
     * @return void
     */
    public function onRun(): void {
        foreach(Server::getInstance()->getOnlinePlayers() as $player){
            if(!$player->isConnected() || !($session = SessionFactory::getInstance()->getSession($player->getName())) instanceof Session){
                continue;
            }
            ScoreboardBuilder::build($session);
        }
        switch(GameArena::getInstance()->getStatus()){
            case GameStatus::PREPARING:
                GameArena::getInstance()->decrementPreparingTime();
                if(GameArena::getInstance()->getPreparingTime() <= 0){
                    GameArena::getInstance()->start();
                }
            break;
            case GameStatus::STARTING;
                GameArena::getInstance()->decrementStartingTime();
                if(GameArena::getInstance()->getStartingTime() === 20){
                    GameArena::getInstance()->teleportToRoom();
                }
                if(GameArena::getInstance()->getStartingTime() === 15 || GameArena::getInstance()->getStartingTime() === 10){
                    GameScatter::getInstance()->scattering();
                }
                if(GameArena::getInstance()->getStartingTime() === 0){
                    GameArena::getInstance()->setStatus(GameStatus::RUNNING);

                    (new GameStartEvent())->call();

                    GameArena::getInstance()->broadcastTitle(TextHelper::replace("&aUHC started"), TextHelper::replace("&7Good luck!"));
                }
            break;
            case GameStatus::RUNNING;
                GameArena::getInstance()->incrementRunningTime();
                if(GameArena::getInstance()->getRunningTime() >= 590 && GameArena::getInstance()->getRunningTime() <= 600){
                    GameArena::getInstance()->broadcastMessage(TextHelper::replace("&7(&l&c!&r&7)&r&e Final heal will occur in: ".Time::getTimeToString(600 - GameArena::getInstance()->getRunningTime())));
                    if(GameArena::getInstance()->getRunningTime() === 600){
                        GameArena::getInstance()->heal();
                    }
                }
                if(GameArena::getInstance()->getRunningTime() >= 1190 && GameArena::getInstance()->getRunningTime() <= 1200){
                    GameArena::getInstance()->broadcastMessage(TextHelper::replace("&7(&l&c!&r&7)&r&e PvP will enabled in: ".Time::getTimeToString(1200 - GameArena::getInstance()->getRunningTime())));
                    if(GameArena::getInstance()->getRunningTime() === 1200){
                        GameArena::getInstance()->broadcastMessage(TextHelper::replace("&7(&l&c!&r&7)&r&a PvP is enabled"));
                    }
                }
                if(GameArena::getInstance()->getRunningTime() >= 2095 && GameArena::getInstance()->getRunningTime() <= 2100){
                    GameArena::getInstance()->broadcastMessage(TextHelper::replace("&7(&l&c!&r&7)&r&e Border will be reduced to ".(GameArena::getInstance()->getBorder() - 250)));
                    if(GameArena::getInstance()->getRunningTime() === 2100){
                        GameArena::getInstance()->setBorder(GameArena::getInstance()->getBorder() - 250);
                        GameArena::getInstance()->teleportToBorder();
                    }
                }
                if(GameArena::getInstance()->getRunningTime() >= 2695 && GameArena::getInstance()->getRunningTime() <= 2700){
                    GameArena::getInstance()->broadcastMessage(TextHelper::replace("&7(&l&c!&r&7)&r&e Border will be reduced to ".(GameArena::getInstance()->getBorder() - 250)));
                    if(GameArena::getInstance()->getRunningTime() === 2700){
                        GameArena::getInstance()->setBorder(GameArena::getInstance()->getBorder() - 250);
                        GameArena::getInstance()->teleportToBorder();
                    }
                }
                if(GameArena::getInstance()->getRunningTime() >= 2995 && GameArena::getInstance()->getRunningTime() <= 3000){
                    GameArena::getInstance()->broadcastMessage(TextHelper::replace("&7(&l&c!&r&7)&r&e Border will be reduced to ".(GameArena::getInstance()->getBorder() - 250)));
                    if(GameArena::getInstance()->getRunningTime() === 3000){
                        GameArena::getInstance()->setBorder(GameArena::getInstance()->getBorder() - 250);
                        GameArena::getInstance()->teleportToBorder();
                    }
                }
                if(GameArena::getInstance()->getRunningTime() >= 3295 && GameArena::getInstance()->getRunningTime() <= 3300){
                    GameArena::getInstance()->broadcastMessage(TextHelper::replace("&7(&l&c!&r&7)&r&e Border will be reduced to ".(GameArena::getInstance()->getBorder() - 150)));
                    if(GameArena::getInstance()->getRunningTime() === 3300){
                        GameArena::getInstance()->setBorder(GameArena::getInstance()->getBorder() - 150);
                        GameArena::getInstance()->teleportToBorder();
                    }
                }
                if(GameArena::getInstance()->getRunningTime() >= 3595 && GameArena::getInstance()->getRunningTime() <= 3600){
                    GameArena::getInstance()->broadcastMessage(TextHelper::replace("&7(&l&c!&r&7)&r&e Border will be reduced to ".(GameArena::getInstance()->getBorder() - 50)));
                    if(GameArena::getInstance()->getRunningTime() === 3600){
                        GameArena::getInstance()->setBorder(GameArena::getInstance()->getBorder() - 50);
                        GameArena::getInstance()->teleportToBorder();
                    }
                }
                if(GameArena::getInstance()->getRunningTime() >= 3895 && GameArena::getInstance()->getRunningTime() <= 3900){
                    GameArena::getInstance()->broadcastMessage(TextHelper::replace("&7(&l&c!&r&7)&r&e Border will be reduced to ".(GameArena::getInstance()->getBorder() - 25)));
                    if(GameArena::getInstance()->getRunningTime() === 3900){
                        GameArena::getInstance()->setBorder(GameArena::getInstance()->getBorder() - 25);
                        GameArena::getInstance()->teleportToBorder();
                    }
                }
                if(GameArena::getInstance()->getRunningTime() > 4000){
                    if(GameArena::getInstance()->getGamemodeType() === GamemodeType::FFA()){
                        if(count(GameArena::getInstance()->getRemainingPlayers()) <= 1){
                            GameArena::getInstance()->setStatus(GameStatus::ENDING);
                        }
                    }elseif(GameArena::getInstance()->getGamemodeType() === GamemodeType::TEAMS()){
                        if(count(GameArena::getInstance()->getRemainingTeams()) <= 1){
                            GameArena::getInstance()->setStatus(GameStatus::ENDING);
                        }
                    }
                }
            break;
            case GameStatus::ENDING;
                if(GameArena::getInstance()->getEndingTime() < 0){
                    GameArena::getInstance()->setEndingTime();
                }
                GameArena::getInstance()->decrementEndingTime();
                if(GameArena::getInstance()->getEndingTime() === 0){
                    GameArena::getInstance()->unprepared();
                    (new GameEndEvent())->call();
                }
            break;
        }
    }
}

?>