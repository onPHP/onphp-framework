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

	final class GoogleGeoResponse implements IteratorAggregate
	{
		protected $name = null;
		protected $status = null;
		protected $placeMarks = null;
		
		/**
		 * @return GoogleGeoResponse
		**/
		public static function createFromSimpleXml(SimpleXMLElement $object)
		{
			$instance = new GoogleGeoResponse();
			
			$instance->
				setName((string) $object->Response->name)->
				setStatus(
					new GoogleGeoStatusCode(
						(int) $object->Response->Status->code
					)
				)->
				setPlaceMarks(
					new GoogleGeoPlacemarkIterator($object->Response->Placemark)
				);
			
			return $instance;
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		/**
		 * @return GoogleGeoResponse
		**/
		public function setName($name)
		{
			$this->name = $name;
			return $this;
		}
		
		/**
		 * @return GoogleGeoStatusCode
		**/
		public function getStatus()
		{
			return $this->status;
		}
		
		/**
		 * @return GoogleGeoResponse
		**/
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
		
		/**
		 * @return GoogleGeoResponse
		**/
		function setPlaceMarks($marks)
		{
			$this->placeMarks = $marks;
			return $this;
		}
	}
?>