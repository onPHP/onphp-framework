<?php

	class BaseHtmlControl
	{
		protected $id      = null;
		protected $name    = null;
		protected $value   = null;
		protected $label   = null;

		public function create()
		{
			return new self;
		}

		public function setId($id)
		{
			$this->id = $id;

			return $this;
		}

		public function getId()
		{
			return $this->id;
		}

		public function setName($name)
		{
			$this->name = $name;

			return $this;
		}

		public function getName()
		{
			return $this->name;
		}

		public function setValue($value)
		{
			$this->value = $value;

			return $this;
		}

		public function getValue()
		{
			return $this->value;
		}

		public function setLabel($label)
		{
			$this->label = $label;

			return $this;
		}

		public function getLabel()
		{
			return $this->label;
		}
	}

?>