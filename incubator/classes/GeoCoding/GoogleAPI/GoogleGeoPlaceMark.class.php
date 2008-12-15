<?php
/***************************************************************************
 *   Copyright (C) 2008 by Shimizu                                    *
 *   neemah86@gmail.com                                                    *
 ***************************************************************************/
/* $Id$ */

	class GoogleGeoPlaceMark
	{
		protected $id = null;
		protected $address = null;
		protected $addressDetails = null;
		protected $point = null;
		
		/**
		 * @param string $id
		 * @return GoogleGeoPlaceMark
		 */
		public function setId($id)
		{
			$this->id = $id;
			return $id;
		}
		
		/**
		 * @return string
		 */
		public function getId()
		{
			return $this->id;
		}
		
		/**
		 * @param string $adr
		 * @return GoogleGeoPlaceMark
		 */
		public function setAddress($adr)
		{
			$this->address = $adr;
			return $this;
		}
		
		/**
		 * @return string
		 */
		public function getAddress()
		{
			return $this->address;
		}
		
		/**
		 * @param GoogleGeoAddressDetail $details
		 * @return GoogleGeoPlaceMark
		 */
		public function setAddressDetails(GoogleGeoAddressDetail $details)
		{
			$this->addressDetails = $details;
			return $this;
		}
		
		/**
		 * @return GoogleGeoAddressDetail
		 */
		public function getAddressDetails()
		{
			return $this->addressDetails;
		}
		
		/**
		 * @param GoogleGeoPoint $point
		 * @return string
		 */
		public function setPoint(GoogleGeoPoint $point)
		{
			$this->point = $point;
			return $this;
		}
		
		/**
		 * @return GoogleGeoPoint
		 */
		public function getPoint()
		{
			return $this->point;
		}
		
		/**
		 * Build object from simpleXMLElement
		 *
		 * @param SimpleXMLElement $object
		 * @return GoogleGeoPlaceMark
		 */
		public static function createFromSimpleXml(SimpleXMLElement $object)
		{
			$instance = new GoogleGeoPlaceMark();
			$instance->setId((string)$object->attributes()->id);
			$instance->setAddress((string)$object->address);
			list($lng, $lat, $z) = explode(',', $object->Point->coordinates);
			$instance->setPoint(new GoogleGeoPoint((float)$lat, (float)$lng, (float)$z));
			$instance->setAddressDetails(GoogleGeoAddressDetail::createFromSimpleXml($object->AddressDetails));
			return $instance;
		}
	}
?>