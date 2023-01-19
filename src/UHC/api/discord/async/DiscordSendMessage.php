<?php

namespace UHC\api\discord\async;

use UHC\api\discord\IDiscord;
use pocketmine\scheduler\AsyncTask;

class DiscordSendMessage extends AsyncTask {
	
	/** @var string */
	protected string $url;
	
	/** @var IDiscord */
	protected IDiscord $content;

	/** @var string */
	protected string $username;

    /**
     * DiscordSendMessage Constructor.
     * @param string $url
     * @param IDiscord $content
     * @param string $username
     */
	public function __construct(string $url, IDiscord $content, string $username){
		$this->url = $url;
		$this->content = $content;
		$this->username = $username;
	}
	
	/**
	 * @return void
	 */
	public function onRun() : void {
		$discord = curl_init();
		curl_setopt($discord, CURLOPT_URL, $this->url);
		curl_setopt($discord, CURLOPT_POSTFIELDS, json_encode($this->content));
		curl_setopt($discord, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
		curl_setopt($discord, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($discord, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($discord);
        curl_error($discord);
	}
}
