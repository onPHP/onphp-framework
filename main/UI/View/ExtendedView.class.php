<?php
/**
 * Универсальный компоновщик вьюх
 * @author Михаил Кулаковский <m@klkvsk.ru>
 * @date 10.07.13
 */

class ExtendedView implements View {
	/** @var View[] */
	protected $before = array();
	/** @var View[] */
	protected $after = array();
	/** @var View */
	protected $body = null;

	protected function __construct(View $body) {
		$this->body = $body;
	}

	public static function create(View $body) {
		return new static($body);
	}

	public function addHeader(View $view) {
		$this->before []= $view;
		return $this;
	}

	public function addFooter(View $view) {
		$this->after []= $view;
		return $this;
	}

	/**
	 * @param $model null or Model
	 * @return self
	 **/
	public function render($model = null) {
		foreach ($this->before as $pre) {
			$pre->render($model);
		}

		$this->body->render($model);

		foreach ($this->after as $post) {
			$post->render($model);
		}

		return $this;
	}


}