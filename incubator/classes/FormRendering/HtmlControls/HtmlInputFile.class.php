<?php

	class HtmlInputFile extends BaseHtmlControl
	{
		public function render()
		{
			return '<input type="file" name="'.$this->name.'" />';
		}
	}

?>