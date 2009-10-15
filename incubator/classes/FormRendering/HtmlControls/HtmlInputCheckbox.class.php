<?php

	class HtmlInputCheckbox extends BaseHtmlControl {
		public function render()
		{
			return '<input type="checkbox" name="'.$this->name.'" value="'.$this->value.'" '.($this->value ? 'checked="checked"' : '').' />';
		}
	}

?>