<?php

	class HtmlTextarea extends BaseHtmlControl
	{
		public function render()
		{
			return '<textarea id="'.$this->id.'" name="'.$this->name.'">'.$this->value.'<textarea>';
		}
	}

?>