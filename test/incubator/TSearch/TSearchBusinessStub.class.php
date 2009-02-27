<?php
	/* $Id$ */

	final class TSearchBusinessStub extends NamedObject implements DAOConnected, TSearchConfigurator
	{
		protected $name = null;
		protected $description = null;
		protected $subNames = array();
		protected $objectList = array();
		
		/**
		 * @return TSearchBusinessStub
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return TSearchBusinessStubDAO
		**/
		public static function dao()
		{
			return Singleton::getInstance('TSearchBusinessStubDAO');
		}
		
		/**
		 * @return TSearchBusinessStub
		**/
		public function setDescription($description)
		{
			$this->description = $description;

			return $this;
		}
		
		public function getDescription()
		{
			return $this->description;
		}
		
		/**
		 * @return TSearchBusinessStub
		**/
		public function setSubnames(array $names)
		{
			$this->subNames = $names;
			
			return $this;
		}
		
		/**
		 * @return array of string
		**/
		public function getSubNames()
		{
			return $this->subNames;
		}
		
		/**
		 * @return TSearchBusinessStub
		**/
		public function setObjectList(array $objectList)
		{
			$this->objectList = $objectList;
			
			return $this;
		}
		
		/**
		 * @return array of Stringable object
		**/
		public function getObjectList()
		{
			return $this->objectList;
		}
		
		/**
		 * @return TSearchData
		**/
		public function getTSearchData()
		{
			return
				TSearchData::create()->
					setFilter(Filter::stripTags())->
					addWeightA($this->getObjectList())->
					addWeightB($this->getSubNames())->
					addWeightC($this->getName())->
					addWeightD($this->getDescription());
		}
	}
?>