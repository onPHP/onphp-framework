<?php

	abstract class BaseControlMapping
	{
		protected $list = null;

		public function create()
		{
			return new self;
		}

		public function getList()
		{
			return $this->list;
		}

		public function mapPrimiitive2Control($primitive, $control)
		{
			
		}

		public function inheritMapping($from)
		{
			
		}
	}

?>