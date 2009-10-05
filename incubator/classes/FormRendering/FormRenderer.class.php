<?php

	class FormRenderer extends Singleton
	{
		private $form            = null;
		private $controlMapping  = null;

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
			//implement me
		}
	}

?>