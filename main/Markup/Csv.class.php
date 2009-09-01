<?php
/***************************************************************************
 *   Copyright (C) 2008 by Michael V. Tchervyakov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Markup
	 * @see http://tools.ietf.org/html/rfc4180
	 * @see also http://php.net/fputcsv
	 * @todo implement parse
	**/
	final class Csv
	{
		const SEPARATOR					= "\x2C";
		const QUOTE						= "\x22";
		const CRLF						= "\x0D\x0A";
		const QUOTE_REQUIRED_PATTERN	= "/(\x2C|\x22|\x0D|\x0A)/";
		
		private $header	= false;
		private $data	= array();
		
		/**
		 * @return Csv
		**/
		public static function create($header = false)
		{
			return new self($header);
		}
		
		public function __construct($header = false)
		{
			$this->header = $header === true;
		}
		
		public function getArray()
		{
			return $this->data;
		}
		
		/**
		 * @return Csv
		**/
		public function setArray($array)
		{
			Assert::isArray($array);
			
			$this->data = $array;
			
			return $this;
		}
		
		public function parse($rawData)
		{
			throw new UnimplementedFeatureException('is not implemented yet');
		}
		
		public function render($forceQuotes = false)
		{
			$csvString	= null;
			
			foreach ($this->data as $row) {
				
				Assert::isArray($row);
				
				$rowString = null;
				
				foreach ($row as $value) {
					if (
						preg_match(self::QUOTE_REQUIRED_PATTERN, $value)
						|| $forceQuotes
					)
						$value =
							self::QUOTE
							.mb_ereg_replace(
								self::QUOTE,
								self::QUOTE.self::QUOTE,
								$value
							)
							.self::QUOTE;
					
					$rowString .=
						(
							$rowString
							? self::SEPARATOR
							: null
						)
						.$value;
				}
				
				$csvString .= $rowString.self::CRLF;
			}
			
			return $csvString;
		}
		
		/**
		 * @return ContentTypeHeader
		**/
		public function getContentTypeHeader()
		{
			return
				ContentTypeHeader::create()->
				setParameter(
					'header',
					$this->header
						? 'present'
						: 'absent'
				)->
				setMediaType('text/csv');
		}
	}
?>