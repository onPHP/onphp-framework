<?php
/***************************************************************************
 *   Copyright (C) 2008 by Shimizu                                    *
 *   neemah86@gmail.com                                                    *
 ***************************************************************************/
/* $Id$ */
	class GoogleGeoResponse implements IteratorAggregate
	{
		protected $name = null;
		protected $status = null;
		protected $placeMarks = null;

		public function getName()
		{
			return $this->name;
		}

		public function setName($name)
		{
			$this->name = $name;
			return $this;
		}

		public function getStatus()
		{
			return $this->status;
		}

		public function setStatus(GoogleGeoStatusCode $status)
		{
			$this->status = $status;
			return $this;
		}

		public function getPlaceMarks()
		{
			return $this->placeMarks;
		}

		public function getIterator()
		{
			return $this->getPlaceMarks();
		}

		function setPlaceMarks($marks)
		{
			$this->placeMarks = $marks;
			return $this;
		}

		public static function createFromSimpleXml(SimpleXMLElement $object)
		{
			$instance = new GoogleGeoResponse();
			$instance->setName((string)$object->Response->name);
			$instance->setStatus(new GoogleGeoStatusCode((int)$object->Response->Status->code));
			$instance->setPlaceMarks(
				new GoogleGeoPlacemarkIterator($object->Response->Placemark)
			);
			return $instance;
		}
		
	}
?>