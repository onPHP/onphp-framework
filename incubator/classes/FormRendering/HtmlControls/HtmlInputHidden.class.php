<?php

	class HtmlInputHidden extends BaseHtmlControl
	{
		public function render()
		{
			return '<input type="text" name="'.$this->name.'" value="'.$this->value.'" />';
		}
	}

?>