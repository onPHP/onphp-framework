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
		 * Build object from SimpleXMLElement
		 * 
		 * @param SimpleXMLElement $object
		 * @return GoogleGeoAdrdressDetail
		**/
		public static function createFromSimpleXml(SimpleXMLElement $object)
		{
			$instance = new GoogleGeoAddressDetail();
			
			$instance->setAccuracy(
				new GoogleGeoAddressAccuracyLevel(
					(int) $object->attributes()->Accuracy
				)
			)->
			setCountry($object->Country);
			
			return $instance;
		}
		
		/**
		 * @see AccuracyLevels to understand
		 * 
		 * @return GoogleGeoAddressAccuracyLevel
		**/
		public function getAccuracy()
		{
			return $this->accuracy;
		}
		
		/**
		 * @return GoogleGeoAddressDetail
		**/
		public function setAccuracy(GoogleGeoAddressAccuracyLevel $accuracy)
		{
			$this->accuracy = $accuracy;
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
		// FIXME: there is no reason to use xml-element instead of just string
		public function setCountry(SimpleXMLElement $country)
		{
			$this->country = $country;
			return $this;
		}
	}
?>