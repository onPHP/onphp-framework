<?php
	/* $Id$ */

	final class TSearchBusinessStubContainer extends NamedObject
	{
		/**
		 * @return TSearchBusinessContainerStub
		**/
		public static function create()
		{
			return new self;
		}
		
		public function toString()
		{
			return $this->name;
		}
	}
?>