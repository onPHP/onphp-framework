<?php
/**
 * Синглтон для рендеринга fenom-шаблонов
 * @author Михаил Кулаковский <m@klkvsk.ru>
 * @date 09.07.13
 */

class FenomRenderer extends Singleton {

	/** @var Fenom */
	protected $fenom = null;

	/** @var FenomMultiProvider */
	protected $provider = null;

	protected function __construct() {
		$this->provider = FenomMultiProvider::create();
		$this->fenom = Fenom::factory($this->provider, ONPHP_TEMP_PATH, Fenom::AUTO_RELOAD);
	}

	/**
	 * @return self
	 */
	public static function me() {
		return self::getInstance(__CLASS__);
	}

	/**
	 * @return Fenom|null
	 */
	public function getFenom (){
		return $this->fenom;
	}

	public function addTemplatePath($path) {
		$this->provider->addProvider(new Fenom\Provider($path));
	}

	public function setCompilePath($path) {
		$this->fenom->setCompileDir($path);
		return $this;
	}

	public function setOptions($mask) {
		$this->fenom->setOptions($mask);
		return $this;
	}

	public function getOption() {
		return $this->fenom->getOptions();
	}

	/**
	 * @param       $template
	 * @param array $data
	 */
	public function render($template, $data = array()) {
		$this->fenom->display($template, $data);
	}
}