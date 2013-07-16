<?php
/**
 * View для шаблонизатора Fenom
 * @author Михаил Кулаковский <m@klkvsk.ru>
 * @date 09.07.13
 */

class FenomView implements View, Stringable {

	protected $template = null;

	protected function __construct() {}

	/**
	 * @param $template
	 * @return self
	 */
	public static function create($template) {
		$view = new static;
		$view->template = $template;
		return $view;
	}

	/**
	 * @param $model null or Model
	 **/
	public function render($model = null) {
		$data = array();
		if ($model instanceof Model) {
			$data = $model->getList();
		}
		FenomRenderer::me()->render($this->template, $data);
	}


	public function toString($model = null)
	{
		try {
			ob_start();
			$this->render($model);
			return ob_get_clean();
		} catch (Exception $e) {
			ob_end_clean();
			throw $e;
		}
	}

}