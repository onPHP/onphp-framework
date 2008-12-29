<?php
/***************************************************************************
 *   Copyright (C) 2008 by Tsyrulnik Y. Viatcheslav                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class GoogleGeoResponse implements IteratorAggregate
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