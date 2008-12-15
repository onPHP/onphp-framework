<?php
/***************************************************************************
 *   Copyright (C) 2008 by Shimizu                                    *
 *   neemah86@gmail.com                                                    *
 ***************************************************************************/
/* $Id$ */

	class GoogleGeoAddressDetail
	{
		protected $accuracy = null;
		protected $country = null;
		
		/**
		 * see AccuracyLevels to understand
		 *
		 * @return int
		 */
		public function getAccuracy()
		{
			return $this->accuracy;
		}
		
		/**
		 * @param GoogleGeoAddressAccuracyLevel $acu
		 * @return GoogleGeoAddressDetail
		 */
		public function setAccuracy(GoogleGeoAddressAccuracyLevel $acu)
		{
			$this->accuracy = $acu;
			return $this;
		}
		
		/**
		 * @return string
		 */
		public function getCountry()
		{
			return $this->country;
		}
		
		/**
		 * @param SimpleXMLElement $country
		 * @return GoogleGeoAddressDetail
		 */
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
		 */
		public static function createFromSimpleXml(SimpleXMLElement $object)
		{
			$instance = new GoogleGeoAddressDetail();
			$instance->setAccuracy(new GoogleGeoAddressAccuracyLevel((int)$object->attributes()->Accuracy));
			$instance->setCountry($object->Country);
			return $instance;
		}
	}
?>