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

	final class GoogleGeoCoding
	{
		/**
		 * geocoding service address
		**/
		const GOOGLE_GEO_SERVICE = 'http://maps.google.com/maps/geo';
		
		/**
		 * Your api key goes here
		 * 
		 * @var string
		**/
		protected $key = null;
		
		/**
		 * output format. for now it works only for xml
		 * 
		 * @var string
		**/
		protected $output = null;
		
		/**
		 * Address we are looking for
		 * 
		 * @var string
		**/
		protected $address = null;
		
		/**
		 * ll, spn, gl pamar string
		 * ll (optional) — The {latitude,longitude} of the viewport center expressed as a comma-separated string (e.g. "ll=40.479581,-117.773438" ). This parameter only has meaning if the spn parameter is also passed to the geocoder.
		 * spn (optional) — The "span" of the viewport expressed as a comma-separated string of {latitude,longitude} (e.g. "spn=11.1873,22.5" ). This parameter only has meaning if the ll parameter is also passed to the geocoder.
		 * gl (optional) — The country code, specified as a ccTLD ("top-level domain") two-character value.
		 * 
		 * @var array
		**/
		protected $additionalParams = null;
		
		
		/**
		 * @param string $key
		 * @param string $output
		**/
		public function __construct($key, $output = 'xml')
		{
			$this->key = $key;
			$this->output = $output;
		}
		
		/**
		 * Setter for additional params, represent get string like "&ll=...&spn=...&gl=..."
		 * 
		 * @param array $ll
		 * @param array $spn
		 * @param string $gl
		 * @return string
		**/
		public function setAdditionalParams($ll = null, $spn = null, $gl = null)
		{
			$addParams = array();
			if (!is_null($ll))
				array_push($addParams,'ll='.$ll);
			if (!is_null($spn))
				array_push($addParams, 'spn='.$spn);
			if (!is_null($gl))
				array_push($addParams, 'gl='.$gl);
				
			if(count($addParams)) {
				return '&'.implode('&');
			}
			return '';
		}
		
		/**
		 * Set address we are looking for
		 * 
		 * @param string $address
		 * @return GoogleGeoCoding
		**/
		public function setAddress($address)
		{
			$this->address = $address;
			return $this;
		}
		
		/**
		 * All magic starts here. Makes request
		 * 
		 * @param bool $returnXmlObject return object as is
		 * @return mixed
		**/
		public function lookup($returnXmlObject = false)
		{
			$result = simplexml_load_file($this->configureParamString());
			$this->proceedRequestStatus($result);
			
			if($returnXmlObject) {
				return $result;
			}
			
			return $this->representGeoObject($result);
		}

		/**
		 * Represent simple xml response as our representation for google response
		 * 
		 * @param simpleXMLElement $obj
		 * @return GoogleGeoResponse
		**/
		public function representGeoObject($obj)
		{
			return GoogleGeoResponse::createFromSimpleXml($obj);
		}
		
		/**
		 * Throw all the crap
		 * 
		 * @param simpleXMLElement $request
		**/
		protected function proceedRequestStatus($request)
		{
			$code = new GoogleGeoStatusCode((int)$request->Response->Status->code);
			switch ($code->getId()) {
				case GoogleGeoStatusCode::GOOGLE_GEO_BAD_KEY:
					throw new GoogleGeoBadKeyException();
				case GoogleGeoStatusCode::GOOGLE_GEO_BAD_REQUEST:
					throw new GoogleGeoBadRequestException();
				case GoogleGeoStatusCode::GOOGLE_GEO_MISSING_ADDRESS:
					throw new GoogleGeoMissingAddressException();
				case GoogleGeoStatusCode::GOOGLE_GEO_MISSING_QUERY:
					throw new GoogleGeoMissingQueryException();
				case GoogleGeoStatusCode::GOOGLE_GEO_SERVER_ERROR:
					throw new GoogleGeoServerErrorException();
				case GoogleGeoStatusCode::GOOGLE_GEO_TOO_MANY_QUERIES:
					throw new GoogleGeoTooManyQueriesException();
				case GoogleGeoStatusCode::GOOGLE_GEO_UNAVAILABLE_ADDRESS:
					throw new GoogleGeoUnavailableAddressException();
				case GoogleGeoStatusCode::GOOGLE_GEO_UNKNOWN_ADDRESS:
					throw new GoogleGeoUnknownAddressException();
				case GoogleGeoStatusCode::GOOGLE_GEO_UNKNOWN_DIRECTIONS:
					throw new GoogleGeoUnknownDirectionsException();
			}
		}
		
		/**
		 * Build request string from params
		 * 
		 * @return string
		**/
		protected function configureParamString()
		{
			$result = '?q='.$this->address;
			$result .= '&key='.$this->key;
			$result .= '&output='.$this->output;
			$result .= $this->additionalParams;
			
			return self::GOOGLE_GEO_SERVICE.$result;
		}
	}
?>