<?php

	class ControlMapping extends StaticFactory {
		public static function html()
		{
			return new HtmlControlMapping();
		}

		public static function custom()
		{
			return new CustomControlMapping();
		}
	}

?>