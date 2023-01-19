<?php

namespace UHC\api\form;

class CustomForm extends IForm {

	/** @var array  */
	protected array $labelMap = [];

	/**
	 * CustomForm Constructor.
	 * @param callable $callable
	 */
	public function __construct(callable $callable){
		parent::__construct($callable);
		$this->data["type"] = "custom_form";
		$this->data["title"] = "";
		$this->data["content"] = [];
	}

    /**
     * @param $data
     * @return void
     */
	public function processDataForm(&$data) : void {
		if(!is_array($data)) return;
		$newData = [];
        foreach($data as $k => $v){
            $newData[$this->labelMap[$k]] = $v;
        }
        $data = $newData;
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
     * @param array $data
     * @return void
     */
    public function putContent(array $data) : void {
        $this->data["content"][] = $data;
    }

    /**
     * @param string $text
     * @param string|null $label
     */
    public function addLabel(string $text, ?string $label = null) : void {
        $this->putContent(["type" => "label", "text" => $text]);
        $this->labelMap[] = $label ?? count($this->labelMap);
    }

    /**
     * @param string $text
     * @param string $placeholder
     * @param string|null $default
     * @param string|null $label
     * @return void
     */
	public function addInput(string $text, string $placeholder = "", string $default = null, ?string $label = null) : void {
		$data = ["type" => "input", "text" => $text, "placeholder" => $placeholder, "default" => $default];
        $this->putContent($data);
		$this->labelMap[] = $label ?? count($this->labelMap);
	}

    /**
     * @param string $text
     * @param array $options
     * @param int|null $default
     * @param string|null $label
     */
    public function addDropdown(string $text, array $options, int $default = null, ?string $label = null) : void {
        $this->putContent(["type" => "dropdown", "text" => $text, "options" => $options, "default" => $default]);
        $this->labelMap[] = $label ?? count($this->labelMap);
    }

    /**
     * @param string $text
     * @param bool|null $default
     * @param string|null $label
     */
    public function addToggle(string $text, bool $default = null, ?string $label = null) : void {
        $content = ["type" => "toggle", "text" => $text];
        if($default !== null) {
            $content["default"] = $default;
        }
        $this->putContent($content);
        $this->labelMap[] = $label ?? count($this->labelMap);
    }

    /**
     * @param string $text
     * @param int $min
     * @param int $max
     * @param int $step
     * @param int $default
     * @param string|null $label
     */
    public function addSlider(string $text, int $min, int $max, int $step = -1, int $default = -1, ?string $label = null) : void {
        $content = ["type" => "slider", "text" => $text, "min" => $min, "max" => $max];
        if($step !== -1) {
            $content["step"] = $step;
        }
        if($default !== -1) {
            $content["default"] = $default;
        }
        $this->putContent($content);
        $this->labelMap[] = $label ?? count($this->labelMap);
    }
}

?>