<?php

	class HtmlSelect extends BaseHtmlControl
	{
		private $list = array();

		public function render()
		{
			$output = '<select name="'.$this->name.'">' . "\n";

			if ($options = $this->list) {
				foreach ($options as $key => $option) {
					$output .=
						'<option value="'.$key.'"'.
						($this->value == $key ? 'selected="selected"' : '').
						'>'.$option.
						"</option>\n";
				}
			}

			$output .= '</select>' . "\n";

			return $output;
		}

		public function setList($list)
		{
			$this->list = $list;
		}
	}

?>