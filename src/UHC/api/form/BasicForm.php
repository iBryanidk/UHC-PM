<?php

namespace UHC\api\form;

class BasicForm extends IForm {

    /** @var int */
	const IMAGE_TYPE_PATH = 0;
    /** @var int */
	const IMAGE_TYPE_URL = 1;

	/** @var string */
	protected string $content = "";

	/** @var array */
	protected array $labelMap = [];

	/**
	 * BasicForm Constructor.
	 * @param callable $callable
	 */
	public function __construct(callable $callable){
		parent::__construct($callable);
		$this->data["type"] = "form";
		$this->data["title"] = "";
		$this->data["content"] = "";
	}

    /**
     * @param $data
     * @return void
     */
	public function processDataForm(&$data) : void {
		$data = $this->labelMap[$data] ?? null;
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
	public function getContent() : string {
		return $this->data["content"];
	}

	/**
	 * @param string $title
	 * @return void
	 */
	public function setTitle(string $title) : void {
		$this->data["title"] = $title;
	}

    /**
     * @return string|null
     */
	public function getTitle() : ?string {
		return $this->data["title"];
	}

    /**
     * @param string $text
     * @param int $imageType
     * @param string $imagePath
     * @param string|null $label
     * @return void
     */
	public function addButton(string $text, int $imageType = -1, string $imagePath = "", ?string $label = null) : void {
		$content = ["text" => $text];
		if($imageType !== -1){
			$content["image"]["type"] = $imageType === 0 ? "path" : "url";
			$content["image"]["data"] = $imagePath;
		}
		$this->data["buttons"][] = $content;
		$this->labelMap[] = $label ?? count($this->labelMap);
	}
}

?>