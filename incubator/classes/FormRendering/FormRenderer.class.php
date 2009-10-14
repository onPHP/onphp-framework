<?php

	class FormRenderer extends Singleton
	{
		private $form            = null;
		private $controlMapping  = null;

		public static function me()
		{
			return self::getInstance(__CLASS__);
		}

		public function setForm(Form $form)
		{
			$this->form = $form;

			return $this;
		}
		
		public function getForm()
		{
			return $this->form;
		}

		public function setControlMapping($controlMapping)
		{
			$this->controlMapping = $controlMapping;

			return $this;
		}

		public function getControlMapping()
		{
			return $this->controlMapping;
		}

		public function render()
		{
			//echo 'Rendering form with '.get_class($this->controlMapping).' mapping.';
			//echo '<pre>'.DebugUtils::ec($this->form->getList());

			foreach ($this->form->getList() as $primitive) {
				$class = get_class($primitive);
				$mappingList = $this->controlMapping->
					getList();

				//$output .= $class . ' - render to - ' . $mappingList[$class] . '<br>';

				echo '<div style="padding: 3px;"><label for="" style="width: 150px; display: block; float: left;">'._($primitive->getName()).':</label>';
				echo $mappingList[$class];
				echo '</div>';
				
			}

			echo HtmlControl::submit();
		}
	}

?>