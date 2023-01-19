<?php

namespace UHC\utils;

use pocketmine\plugin\PluginException;

final class Time {

    /** @var string[] */
	const formats = ["minutes", "hours", "seconds", "days"];

    /**
     * @param int $initialTime
     * @param int $incrementerTime
     * @return int
     */
    public static function incrementTime(int $initialTime, int $incrementerTime) : int {
        return $initialTime + $incrementerTime;
    }

    /**
     * @param int $initialTime
     * @param int $reducerTime
     * @return int
     */
    public static function decrementTime(int $initialTime, int $reducerTime) : int {
        return $initialTime - $reducerTime;
    }

    /**
     * @param int $time
     * @return string
     */
    public static function getTimeToString(int $time) : string {
		$m = null;		
		$h = null;
        $d = null;
		if($time >= 60){			
			$m = floor(($time % 3600) / 60);		
			if($time >= 3600){				
				$h = floor(($time % 86400) / 3600);
                if($time >= 3600 * 24){
                    $d = floor($time / 86400);
                }
			}		
		}		
        if(!is_null($m)){
            $format = "i:s";
        }
        if(!is_null($h)){
            $format = "H:i:s";
        }
        if(!is_null($d)){
            $format = "d:H:i:s";
        }
		return gmdate($format ?? "s", $time);
	}

    /**
     * @param int $time
     * @return string
     */
    public static function getTimeToFullString(int $time) : string {
		$s = $time % 60;	
		$m = null;		
		$h = null;		
		$d = null;
		
		if($time >= 60){			
			$m = floor(($time % 3600) / 60);		
			if($time >= 3600){				
				$h = floor(($time % 86400) / 3600);				
				if($time >= 3600 * 24){					
					$d = floor($time / 86400);					
				}			
			}		
		}		
		return ($m !== null ? ($h !== null ? ($d !== null ? "$d days " : "")."$h hours " : "")."$m minutes " : "")."$s seconds";
	}

    /**
     * @param string $input
     * @return int|null
     */
    public static function stringToInt(string $input) : ?int {
        $characters = null;
        $result = str_split($input);
        for($i = 0; $i < count($result); $i++){
            if(is_numeric($result[$i])){
            	$characters .= $result[$i];
            }
        }
        return $characters;
    }

    /**
     * @param string $input
     * @return string
     */
    public static function stringInputToStringFormat(string $input) : string {
        $result = str_split($input);
        for($i = 0; $i < count($result); $i++){
            switch($result[$i]){
                case "m":
                $format = "minutes";
                break;
                case "h":
                $format = "hours";
                break;
                case "d":
                $format = "days";
                break;
                case "s":
                $format = "seconds";
                break;
            }
        }
        return $format ?? "";
    }

    /**
     * @param string $input
     * @return bool
     */
    public static function validInput(string $input) : bool {
        return self::stringToInt($input).substr(self::stringInputToStringFormat($input), 0, strlen(self::stringInputToStringFormat($input)) - strlen(self::stringInputToStringFormat($input)) + 1) === $input;
    }

    /**
     * @param int $time
     * @param string $input
     * @return int
     */
    public static function stringFormatToFinalTime(int $time, string $input) : int {
        return match(self::stringInputToStringFormat($input)){
            "minutes" => $time * 60,
            "hours" => $time * 3600,
            "days" => $time * 86400,
            "seconds" => $time,
            default => throw new PluginException("Time format you're entering does exists"),
        };
    }
}

?>