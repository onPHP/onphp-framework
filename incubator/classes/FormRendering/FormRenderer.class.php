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
			$output = '';
			
			foreach ($this->form->getList() as $primitive) {
				$class = get_class($primitive);
				$mappingList = $this->controlMapping->
					getList();


				//echo '<pre>';
				//DebugUtils::ec($primitive);


				$name = $primitive->getName();


/*
if ($primitive instanceof PrimitiveList) {
	print_r($primitive->getList());
	print_r($primitive->getValue());
}
*/
	
	//$output .= $primitive->isImported();

				$value = $primitive->isImported()
					? $primitive->getValue()
					: null;

				$output .= '<div style="padding: 3px; border: 1px solid #ссс; border-bottom: none; clear: both;">' . "\n";

				if (!($mappingList[$class] instanceof HtmlInputHidden)) {
					$output .= '<label for="'.$name.'" style="width: 150px; display: block; float: left;">'._($name).':</label>' . "\n";
				}

				$control = $mappingList[$class]->
					setValue($value)->
					setName($name);

				if ($primitive instanceof PrimitiveList) {
					$control->setList(
						$primitive->
							getList()
					);
				}

				$output .= $control->render();
				
				$output .=  "\n" . '</div>' . "\n";
			}

			return $output;
		}
	}

?>