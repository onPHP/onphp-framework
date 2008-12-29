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

	final class GoogleGeoAddressDetail
	{
		protected $accuracy = null;
		protected $country = null;
		
		/**
		 * see AccuracyLevels to understand
		 * 
		 * @return int
		**/
		public function getAccuracy()
		{
			return $this->accuracy;
		}
		
		/**
		 * @param GoogleGeoAddressAccuracyLevel $acu
		 * @return GoogleGeoAddressDetail
		**/
		public function setAccuracy(GoogleGeoAddressAccuracyLevel $acu)
		{
			$this->accuracy = $acu;
			return $this;
		}
		
		/**
		 * @return string
		**/
		public function getCountry()
		{
			return $this->country;
		}
		
		/**
		 * @param SimpleXMLElement $country
		 * @return GoogleGeoAddressDetail
		**/
		public function setCountry(SimpleXMLElement $country)
		{
			$this->country = $country;
			return $this;
		}
		
		/**
		 * Build object from simpleXMLElement
		 * 
		 * @param SimpleXMLElement $object
		 * @return GoogleGeoAdrdressDetail
		**/
		public static function createFromSimpleXml(SimpleXMLElement $object)
		{
			$instance = new GoogleGeoAddressDetail();
			$instance->setAccuracy(new GoogleGeoAddressAccuracyLevel((int)$object->attributes()->Accuracy));
			$instance->setCountry($object->Country);
			return $instance;
		}
	}
?>