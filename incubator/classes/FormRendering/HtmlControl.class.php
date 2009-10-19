<?php

	class HtmlControl extends StaticFactory
	{
		public static function text()
		{
			return new HtmlInputText;
		}

		public static function file()
		{
			return new HtmlInputFile;
		}

		public static function hidden()
		{
			return new HtmlInputHidden;
		}

		public static function submit()
		{
			return new HtmlInputSubmit;
		}

		public static function checkbox()
		{
			return new HtmlInputCheckbox;
		}

		public static function select()
		{
			return new HtmlSelect;
		}
	}

?>