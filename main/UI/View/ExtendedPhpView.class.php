<?php
/**
 * Расширенная вьюха с прелоадом и постлоадом
 * @author Alex Gorbylev <alex@gorbylev.ru>
 * @date 2012.03.20
 */
class ExtendedPhpView extends SimplePhpView {

	protected
		/**
		 * @var PartViewer
		 */
		$partViewer = null,
		/**
		 * вьюшка, которую выпаолним до основной
		 * @var string
		 */
		$preView = null,
		/**
		 * вьюшка, которую выполним после основной
		 * @var string
		 */
		$postView = null;

	/**
	 * @param string $preView
	 * @return ExtendedPhpView
	 */
	public function setPreView($preView) {
		$this->preView = $preView;
		return $this;
	}

	/**
	 * @param string $postView
	 * @return ExtendedPhpView
	 */
	public function setPostView($postView) {
		$this->postView = $postView;
		return $this;
	}

	/**
	 * @param Model $model
	 * @return ExtendedPhpView
	 */
	public function render(/* Model */ $model = null)
	{
		Assert::isTrue($model === null || $model instanceof Model);

		if ($model)
			extract($model->getList());

		$this->partViewer = $partViewer = new PartViewer($this->partViewResolver, $model);

		$this->preRender();

		include $this->templatePath;

		$this->postRender();

		return $this;
	}

	protected function preRender() {
		if( !is_null($this->preView) ) {
			$this->partViewer->view($this->preView);
		}
		return $this;
	}

	protected function postRender() {
		if( !is_null($this->postView) ) {
			$this->partViewer->view($this->postView);
		}
		return $this;
	}

}
