<?php
/***************************************************************************
 *   Copyright (C) 2007 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Http
	**/
	final class HeaderParser
	{
		private $headers		= array();
		private $currentHeader	= null;

		/**
		 * @deprecated
		 * @return HeaderParser
		 */
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @param raw header data
		 * @return associative array of headers (name => value)
		**/
		public function parse($data)
		{
			$lines = explode("\n", $data);
			
			foreach ($lines as $line) {
				$this->doLine($line);
			}
			
			return $this;
		}
		
		public function doLine($line)
		{
			$line = trim($line, "\r\n");
			$matches = array();

			if (preg_match("/^([\w-]+):\s+(.+)/", $line, $matches)) {
				
				$name = strtolower($matches[1]);
				$value = $matches[2];
				$this->currentHeader = $name;

				if (isset($this->headers[$name])) {
					if (!is_array($this->headers[$name])) {
						$this->headers[$name] = array($this->headers[$name]);
					}
					$this->headers[$name][] = $value;
				} else {
					$this->headers[$name] = $value;
				}
				
			} elseif (
				preg_match("/^\s+(.+)$/", $line, $matches)
				&& $this->currentHeader !== null
			) {
				if (is_array($this->headers[$this->currentHeader])) {
					$lastKey = count($this->headers[$this->currentHeader]) - 1;
					$this->headers[$this->currentHeader][$lastKey]
						.= $matches[1];
				} else {
					$this->headers[$this->currentHeader] .= $matches[1];
				}
			}
			
			return $this;
		}
		
		/**
		 * @return associative array of headers (name => value)
		**/
		public function getHeaders()
		{
			return $this->headers;
		}
		
		public function hasHeader($name)
		{
			return isset($this->headers[strtolower($name)]);
		}
		
		public function getHeader($name)
		{
			return $this->headers[strtolower($name)];
		}
	}
?>