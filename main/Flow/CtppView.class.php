<?php
/**
 * View using CT++ module
 * @see http://ctpp.havoc.ru/
 * @see http://ctpp.havoc.ru/php-ctpp-api.html
 * @author Alex Gorbylev <alex@gorbylev.ru>
 * @date 2011.12.25
 */
class CtppView implements View, Stringable {

	/**
	 * @var ctpp
	 */
	protected $ctpp = null;

	/**
	 * @var string
	 */
	protected $bytecode = null;

	/**
	 * @var array
	 */
	protected $templatePaths = array();

	/**
	 * @var null
	 */
	protected $templateName = null;

	/**
	 * @var string
	 */
	protected $templateExt = 'tmpl';

	/**
	 * @var int
	 */
	protected $steps_limit = null;

	public static function create() {
		return new self();
	}

	public function __construct() {
		// проверяем подключен ли модуль CT++
		if( !extension_loaded('ctpp') ) {
			throw new MissingModuleException('CT++ module not found!');
		}
		// creating ct++
		$this->ctpp = new ctpp();
	}

	/**
	 * @param string $templateName
	 * @return CtppView
	 */
	public function setTemplateName($templateName) {
		$this->templateName = $templateName;
		return $this;
	}

	/**
	 * @param array $templatePaths
	 * @return CtppView
	 */
	public function setTemplatePaths(array $templatePaths) {
		$this->templatePaths = $templatePaths;
		return $this;
	}

	/**
	 * @param string $templatePath
	 * @return CtppView
	 */
	public function addTemplatePath($templatePath) {
		$this->templatePaths[] = $templatePath;
		return $this;
	}

	/**
	 * @param string $templateExt
	 * @return CtppView
	 */
	public function setTemplateExt($templateExt) {
		$this->templateExt = $templateExt;
		return $this;
	}

	/**
	 * @param int $steps_limit
	 * @return CtppView
	 */
	public function setStepsLimit($steps_limit) {
		if( is_int($steps_limit) ) {
			$this->steps_limit = $steps_limit;
		} else {
			throw new WrongArgumentException('Steps limit nust be integer!');
		}

		return $this;
	}

	/**
	 * @param Model
	 * @return mixed
	 */
	public function render(/* Model */ $model = null) {
		$this->makeByteCode();
		$this->setData( $model );
		return $this->ctpp->output( $this->bytecode );
	}

	/**
	 * @param Model
	 * @return mixed
	 */
	public function toString(/* Model */ $model = null) {
		$this->makeByteCode();
		$this->setData( $model );
		$result = $this->ctpp->output_string( $this->bytecode );
		if( $result === false ) {
			throw new CtppException( $this->ctpp->get_last_error() );
		}
		return $result;
	}

	private function makeByteCode() {
		if( empty($this->templatePaths) ) {
			throw new WrongStateException('Template paths are not defined!');
		}
		if( empty($this->templateName) ) {
			throw new WrongStateException('Template name is not defined!');
		}
		// TODO: прикрутить кэширование байткода
		// making bytecode
		$this->ctpp->include_dirs( $this->templatePaths );
		$this->bytecode = $this->ctpp->parse_template( $this->templateName.'.'.$this->templateExt );
	}

	private function setData(Model $model = null) {
		$this->ctpp->emit_params( $model->getList() );
	}

}
