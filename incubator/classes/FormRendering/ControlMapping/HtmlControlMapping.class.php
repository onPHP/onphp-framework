<?php

	final class HtmlControlMapping extends BaseControlMapping
	{
		public function getList()
		{
			return array(
				'PrimitiveIntegerIdentifier'  => HtmlControl::hidden(),
				'PrimitiveString'             => HtmlControl::text(),
				'PrimitiveInteger'            => HtmlControl::text(),
				'PrimitiveFile'               => HtmlControl::file(),
				'PrimitiveChoice'             => HtmlControl::select(),
			);
		}
	}

?>